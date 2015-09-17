<?php
namespace AppBundle\Command;

use AppBundle\Entity\ExternalCategory;
use AppBundle\Entity\Product;
use AppBundle\Entity\Vendor;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;

class ParseCommand extends ContainerAwareCommand
{
    protected $output;

    protected $delimer = '----------';

    protected $ctx;

    protected function configure()
    {
        $this
            ->setName('kins:parse')
            ->setDescription('Parse markets')
            ->addArgument(
                'marketId',
                InputArgument::OPTIONAL,
                'Who do you want to parse?'
            )
//            ->addOption(
//                'marketId',
//                null,
//                InputOption::VALUE_OPTIONAL,
//                'If set, i`ll parse it'
//            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->ctx = stream_context_create();
        stream_context_set_params($this->ctx, array("notification" => array($this, 'stream_notification_callback')));
        $marketId = intval($input->getArgument('marketId'));
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        if ($marketId) {
            $sites = $em
                ->getRepository('AppBundle:Site')
                ->findBy(array('id' => $marketId));
        } else {
            $sites = $em
                ->getRepository('AppBundle:Site')
                ->findAll();
        }

        foreach ($sites as $site) {
            $siteId = $site->getId();
            $this->outputWriteLn('Start parse market ' . $siteId . '.');
            $newVersion = $site->getVersion() + 0.01;
            $site->setVersion($newVersion);
//            $nowDate = new \DateTime('NOW');
//            $lastParseDate = $site->getLastParseDate();
//                if ($lastParseDate->diff($nowDate)->format('%h') < $site->getUpdatePeriod()) {
//                    $output->writeln($delimer . 'Skip parse market ' . $siteId . '. Memory usage: ' . (memory_get_usage() / 1024) . ' KB' . $delimer);
//                    continue;
//                }

            $this->outputWriteLn('Start download xml.');
            $xmlContent = file_get_contents($site->getXmlParseUrl(), false, $this->ctx);
            $this->outputWriteLn('End download xml.');

            $crawler = new Crawler($xmlContent);
            $site->setLastParseDate(new \DateTime());
            $em->persist($site);
            $em->flush();

            $this->outputWriteLn('Start parse categories.');
            $externalCategoriesInfo = $crawler
                ->filterXPath('//categories/category')
                ->each(function (Crawler $nodeCrawler) {
                    $resultArray['externalId'] = $nodeCrawler->attr('id');
                    $resultArray['parentId'] = $nodeCrawler->attr('parentId') ? $nodeCrawler->attr('parentId') : 0;
                    foreach ($nodeCrawler as $node) {
                        $resultArray[$node->nodeName] = $node->nodeValue;
                    }
                    return $resultArray;
                });
            $newExternalCategoriesArray = array();
            foreach ($externalCategoriesInfo as $externalCategory) {
                $newExternalCategory = null;
                $oldExternalCategory = $em
                    ->getRepository('AppBundle:ExternalCategory')
                    ->findOneBy(array(
                        'externalId' => $externalCategory,
                        'site' => $siteId,
                    ));
                if (!$oldExternalCategory) {
                    $newExternalCategory = new ExternalCategory();
                    $newExternalCategoriesArray[] = $externalCategory;
                } else {
                    $newExternalCategory = $oldExternalCategory;
                }
                $newExternalCategory->setVersion($newVersion);
                $newExternalCategory->setExternalId($externalCategory['externalId']);
                $newExternalCategory->setName($externalCategory['category']);
                $newExternalCategory->setSite($site);
                $newExternalCategory->setParentId($externalCategory['parentId']);
                $em->persist($newExternalCategory);
            }
            $em->flush();
            $em->clear('AppBundle\Entity\ExternalCategory');
            $this->outputWriteLn('New Categories from XML - ' . count($newExternalCategoriesArray) . '.');
            $this->outputWriteLn('End parse categories');

            $this->outputWriteLn('Start parse offers');
            $productsInfo = $crawler
                ->filterXPath('//offers/offer')
                ->each(function (Crawler $nodeCrawler) {
                    $children = $nodeCrawler->children();
                    $resultArray['externalId'] = $nodeCrawler->attr('id');
                    foreach ($children as $child) {
                        if ($child->nodeName == 'picture') {
                            $resultArray['pictures'][] = $child->nodeValue;
                        } else {
                            $resultArray[$child->nodeName] = $child->nodeValue;
                        }
                    }
                    return $resultArray;
                });
            $this->outputWriteLn('End parse offers');
            $this->outputWriteLn('Start import vendors.');
            $i = 0;
            $newVendors = array();
            foreach ($productsInfo as $product) {
                $oldVendor = null;
                $vendor = null;
                if ($i % 1000 == 0) {
                    $em->flush();
                    $em->clear('AppBundle\Entity\Vendor');
                    $this->outputWriteLn('Vendors - ' . $i . '.');
                }
                if (isset($product['vendor'])) {
                    if (in_array($product['vendor'], $newVendors)) {
                        continue;
                    }
                    $oldVendor = $em->getRepository('AppBundle:Vendor')
                        ->findOneBy(array (
                            'name' => $product['vendor'],
                            'site' => $siteId,
                        ));
                    if (!$oldVendor) {
                        $vendor = new Vendor();
                        $newVendors[] = $product['vendor'];
                    } else {
                        $vendor = $oldVendor;
                    }
                    $vendor->setVersion($newVersion);
                    if (isset($product['vendorCode'])) {
                        $vendor->setCode($product['vendorCode']);
                    }
                    $vendor->setSite($site);
                    $vendor->setName($product['vendor']);
                    $em->persist($vendor);
                    $i++;
                }
            }
            $em->flush();
            $em->clear('AppBundle\Entity\Vendor');
            $this->outputWriteLn('End import vendors.');
            $this->outputWriteLn('Imported vendors - ' . count($newVendors) . '.');
            $i = 0;
            $this->outputWriteLn('Start import offers.');
            foreach ($productsInfo as $product) {
                $oldProduct = null;
                $newProduct = null;
                $externalCategory = null;
                $oldVendor = null;
                if ($i % 1000 == 0) {
                    $em->flush();
                    $em->clear('AppBundle\Entity\Product');
                    $this->outputWriteLn('Offers - ' . $i . '.');
                }
                $oldProduct = $em
                    ->getRepository('AppBundle:Product')
                    ->findOneBy(array(
                        'externalId' => $product['externalId'],
                        'site' => $siteId,
                    ));
                if (!$oldProduct) {
                    $newProduct = new Product();
                } else {
                    $newProduct = $oldProduct;
                }
                $newProduct->setVersion($newVersion);
                $newProduct->setExternalId($product['externalId']);
                $newProduct->setSite($site);
                if (isset($product['name'])) {
                    $newProduct->setName($product['name']);
                }
                $externalCategory = $em
                    ->getRepository('AppBundle:ExternalCategory')
                    ->findOneBy(array(
                        'externalId' => $product['categoryId'],
                        'site' => $siteId,
                    ));
                if (!$externalCategory) {
                    $noCategoryArray[] = $product['categoryId'];
                } else {
                    $newProduct->setCategory($externalCategory);
                }
                if (isset($product['currencyId'])) {
                    $newProduct->setCurrencyId($product['currencyId']);
                }
                if (isset($product['description'])) {
                    $newProduct->setDescription($product['description']);
                }
                if (isset($product['model'])) {
                    $newProduct->setModel($product['model']);
                }
                if (isset($product['modified_time'])) {
                    $newProduct->setModifiedTime($product['modified_time']);
                }
                if (isset($product['price'])) {
                    $newProduct->setPrice($product['price']);
                }
                if (isset($product['typePrefix'])) {
                    $newProduct->setTypePrefix($product['typePrefix']);
                }
                if (isset($product['url'])) {
                    $newProduct->setUrl($product['url']);
                }
                if (isset($product['pictures'])) {
                    $newProduct->setPictures($product['pictures']);
                }
                if (isset($product['vendor'])) {
                    $oldVendor = $em->getRepository('AppBundle:Vendor')
                        ->findOneBy(array (
                            'name' => $product['vendor'],
                            'site' => $siteId,
                        ));
                    if (!$oldVendor) {
                        $noVendorsArray[] = $product['vendor'];
                    } else {
                        $vendor = $oldVendor;
                        $vendor->setVersion($newVersion);
                        if (isset($product['vendorCode'])) {
                            $vendor->setCode($product['vendorCode']);
                        }
                        $vendor->setSite($site);
                        $vendor->setName($product['vendor']);
                        $em->persist($vendor);
                        $newProduct->setVendor($vendor);
                    }
                }
                $em->persist($newProduct);
                $i++;
            }
            $this->outputWriteLn('End import offers.');
            if (isset($noCategoryArray)) {
                $this->outputWriteLn('No categories - ' . count($noCategoryArray) . '.');
            }
            if (isset($noVendorsArray)) {
                $this->outputWriteLn('No vendors - ' . count($noVendorsArray) . '.');
            }
            $this->outputWriteLn('Imported offers - ' . count($productsInfo) . '.');
            $this->outputWriteLn('End parse market ' . $siteId . '.');
        }

    }

    private function outputWriteLn($text)
    {
        $newTimeDate = new \DateTime();
        $newTimeDate = $newTimeDate->format(\DateTime::ATOM);
        $this->output->writeln($this->delimer. $newTimeDate . ' ' . $text . ' Memory usage: ' . round(memory_get_usage() / (1024 * 1024)) . ' MB' . $this->delimer);
    }

    private function stream_notification_callback($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) {
        switch($notification_code) {
            case STREAM_NOTIFY_RESOLVE:
            case STREAM_NOTIFY_AUTH_REQUIRED:
            case STREAM_NOTIFY_COMPLETED:
                printf("\r\n");
                break;
            case STREAM_NOTIFY_FAILURE:
            case STREAM_NOTIFY_AUTH_RESULT:
//            var_dump($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max);
                /* Игнорируем */
                break;
            case STREAM_NOTIFY_REDIRECTED:
                /* Игнорируем */
                break;
            case STREAM_NOTIFY_CONNECT:
                /* Игнорируем */
                break;
            case STREAM_NOTIFY_FILE_SIZE_IS:
                /* Игнорируем */
                break;
            case STREAM_NOTIFY_MIME_TYPE_IS:
                /* Игнорируем */
                break;
            case STREAM_NOTIFY_PROGRESS:
                $fileSize = round($bytes_transferred / (1024 * 1024), 1);
                printf("\r" . $this->delimer . 'Download: ' . $fileSize . ' MB.' . ' Memory usage: ' . round(memory_get_usage() / (1024 * 1024)) . ' MB' .  $this->delimer);
                break;
        }
    }
}