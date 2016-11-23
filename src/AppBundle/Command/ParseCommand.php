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

    protected $em;

    protected $noCategoryArray;

    protected $noVendorsArray;

    protected $newProducts;

    protected $updatedProducts;

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
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        if ($marketId) {
            $sites = $this->em
                ->getRepository('AppBundle:Site')
                ->findBy(array('id' => $marketId));
        } else {
            $sites = $this->em
                ->getRepository('AppBundle:Site')
                ->findAll();
        }

        foreach ($sites as $site) {
            $this->noCategoryArray = array();
            $this->noVendorsArray = array();
            $this->newProducts = array();
            $this->updatedProducts = array();
            $siteId = $site->getId();
            $this->outputWriteLn('Start parse market ' . $siteId . '.');
            $newVersion = $site->getVersion() + 0.01;
            $site->setVersion($newVersion);

            $xmlContent = file_get_contents($site->getXmlParseUrl(), false, $this->ctx);
//            $xmlContent = file_get_contents('test.xml', false, $this->ctx);
            print_r("\n");

            $crawler = new Crawler($xmlContent);
            $site->setLastParseDate(new \DateTime());
            $this->em->persist($site);
            $this->em->flush();

            //---------------------- Parse categories ----------------------
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
            $newExternalCategoriesArray = $this->parseCategories($externalCategoriesInfo, $site, $newVersion);
            $this->outputWriteLn('New Categories from XML - ' . count($newExternalCategoriesArray) . '.');
            //---------------------- Parse categories ----------------------

            //---------------------- Parse offers ----------------------
            $productsInfo = $crawler
                ->filterXPath('//offers/offer')
                ->each(function (Crawler $nodeCrawler) {
                    $children = $nodeCrawler->children();
                    $resultArray['externalId'] = $nodeCrawler->attr('id');
                    foreach ($children as $child) {
                        if ($child->nodeName == 'picture') {
                            $resultArray['pictures'][] = $child->nodeValue;
                        } elseif ($child->getAttribute('name') == 'Размер') {
                            $resultArray['params']['size'] = $child->nodeValue;
                        } elseif ($child->getAttribute('name') == 'Цвет') {
                            $resultArray['params']['color'] = $child->nodeValue;
                        } elseif ($child->getAttribute('name') == 'Скидка') {
                            $resultArray['params']['discont'] = $child->nodeValue;
                        } else {
                            $resultArray[$child->nodeName] = $child->nodeValue;
                        }
                    }
                    return $resultArray;
                });
            //---------------------- Parse offers ----------------------

            //---------------------- Import vendors ----------------------
            $newVendors = $this->parseVendors($productsInfo, $site, $newVersion);
            $this->em->flush();
            $this->em->clear('AppBundle\Entity\Vendor');
            $this->outputWriteLn('Imported vendors - ' . count($newVendors) . '.');
            //---------------------- Import vendors ----------------------

            //---------------------- Import offers ----------------------
            $this->importProducts($productsInfo, $site, $newVersion);
            if (isset($this->noCategoryArray)) {
                $this->outputWriteLn('No categories - ' . count($this->noCategoryArray) . '.');
            }
            if (isset($this->noVendorsArray)) {
                $this->outputWriteLn('No vendors - ' . count($this->noVendorsArray) . '.');
            }
            if (isset($this->newProducts)) {
                $this->outputWriteLn('New offers - ' . count($this->newProducts) . '.');
            }
            if (isset($this->updatedProducts)) {
                $this->outputWriteLn('Updated offers - ' . count($this->updatedProducts) . '.');
            }
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

    private function parseCategories($externalCategoriesInfo, $site, $newVersion)
    {
        $newExternalCategoriesArray = array();
        foreach ($externalCategoriesInfo as $externalCategory) {
            $newExternalCategory = null;
            $oldExternalCategory = $this->em
                ->getRepository('AppBundle:ExternalCategory')
                ->findOneBy(array(
                    'externalId' => $externalCategory['externalId'],
                    'site' => $site->getId(),
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
            $this->em->persist($newExternalCategory);
        }
        $this->em->flush();
        $this->em->clear('AppBundle\Entity\ExternalCategory');
        return $newExternalCategoriesArray;
    }

    private function parseVendors($productsInfo, $site, $newVersion)
    {
        $i = 0;
        $newVendors = array();
        foreach ($productsInfo as $product) {
            $oldVendor = null;
            $vendor = null;
            if ($i % 10000 == 0) {
                $this->em->flush();
                $this->em->clear('AppBundle\Entity\Vendor');
                $this->outputWriteLn('Vendors scan in ' . $i . ' offers.');
            }
            if (isset($product['vendor'])) {
                if (in_array($product['vendor'], $newVendors)) {
                    continue;
                }
                $oldVendor = $this->em->getRepository('AppBundle:Vendor')
                    ->findOneBy(array (
                        'name' => $product['vendor'],
                        'site' => $site->getId(),
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
                $this->em->persist($vendor);
                $i++;
            }
        }
        $this->em->flush();
        $this->em->clear('AppBundle\Entity\Vendor');

        return $newVendors;
    }

    private function importProducts($productsInfo, $site, $newVersion)
    {
        $i = 0;
        foreach ($productsInfo as $product) {
            $oldProduct = null;
            $newProduct = null;
            $externalCategory = null;
            $oldVendor = null;
            $oldProduct = $this->em
                ->getRepository('AppBundle:Product')
                ->findOneBy(array(
                    'externalId' => $product['externalId'],
                    'site' => $site->getId(),
                ));
            if (!$oldProduct) {
                $newProduct = new Product();
                $this->newProducts[] = $newProduct;
                if (isset($product['description'])) {
                    $newProduct->setDescription($product['description']);
                }
            } else {
                $newProduct = $oldProduct;
                $this->updatedProducts[] = $newProduct;
            }
            if (isset($product['params'])) {
                $newProduct->setParams($product['params']);
            }
            $newProduct->setVersion($newVersion);
            $newProduct->setExternalId($product['externalId']);
            $newProduct->setSite($site);
            $newProduct->setIsDelete(false);
            $newProduct->setUpdated(new \DateTime());
            if (isset($product['name'])) {
                $newProduct->setName($product['name']);
            }
            $externalCategory = $this->em
                ->getRepository('AppBundle:ExternalCategory')
                ->findOneBy(array(
                    'externalId' => $product['categoryId'],
                    'site' => $site->getId(),
                ));
            if (!$externalCategory) {
                $this->noCategoryArray[] = $product['categoryId'];
            } else {
                $newProduct->setCategory($externalCategory);
            }
            if (isset($product['currencyId'])) {
                $newProduct->setCurrencyId($product['currencyId']);
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
                $oldVendor = $this->em->getRepository('AppBundle:Vendor')
                    ->findOneBy(array (
                        'name' => $product['vendor'],
                        'site' => $site->getId(),
                    ));
                if (!$oldVendor) {
                    $this->noVendorsArray[] = $product['vendor'];
                } else {
                    $vendor = $oldVendor;
                    $vendor->setVersion($newVersion);
                    if (isset($product['vendorCode'])) {
                        $vendor->setCode($product['vendorCode']);
                    }
                    $vendor->setSite($site);
                    $vendor->setName($product['vendor']);
                    $this->em->persist($vendor);
                    $newProduct->setVendor($vendor);
                }
            }
            $this->em->persist($newProduct);
            if ($i % 10000 == 0) {
                $this->em->flush();
                $this->em->clear('AppBundle\Entity\Product');
                $this->outputWriteLn('Offers - ' . $i . '.');
            }
            $i++;
        }
        $this->em->flush();
        $this->em->clear('AppBundle\Entity\Product');
    }
}