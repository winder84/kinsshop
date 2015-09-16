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
        $marketId = $input->getArgument('marketId');
        $em = $this->getContainer()->get('doctrine')->getManager();
        if ($marketId) {
            $sites = $em
                ->getRepository('AppBundle:Site', $marketId);
        } else {
            $sites = $em
                ->getRepository('AppBundle:Site')
                ->findAll();
        }

        foreach ($sites as $site) {
            $siteId = $site->getId();
            $nowDate = new \DateTime('NOW');
            $delimer = ' ---------- ';
            $output->writeln($delimer . 'Start parse market ' . $siteId . '. Memory usage: ' . (memory_get_usage() / 1024) . ' KB' . $delimer);
            $newVersion = $site->getVersion() + 0.01;
            $site->setVersion($newVersion);
            $lastParseDate = $site->getLastParseDate();
//                if ($lastParseDate->diff($nowDate)->format('%h') < $site->getUpdatePeriod()) {
//                    $output->writeln($delimer . 'Skip parse market ' . $siteId . '. Memory usage: ' . (memory_get_usage() / 1024) . ' KB' . $delimer);
//                    continue;
//                }

            $output->writeln($delimer . 'Start download xml. Memory usage: ' . (memory_get_usage() / 1024) . ' KB' . $delimer);
            $xmlContent = file_get_contents($site->getXmlParseUrl());
//            $xmlContent = file_get_contents($this->getContainer()->get('kernel')->getRootDir() . '/../web/akusherstvo_products_20150915_003939.xml');
            $output->writeln($delimer . 'End download xml. Memory usage: ' . (memory_get_usage() / 1024) . ' KB' . $delimer);

            $crawler = new Crawler($xmlContent);
            $site->setLastParseDate(new \DateTime());
            $em->persist($site);
            $em->flush();

            $output->writeln($delimer . 'Start parse categories. Memory usage: ' . (memory_get_usage() / 1024) . ' KB' . $delimer);
            $externalCategoriesInfo = $crawler
                ->filterXPath('//categories/category')
                ->each(function (Crawler $nodeCrawler) {
                    $resultArray['externalId'] = $nodeCrawler->attr('id');
                    $resultArray['parentId'] = $nodeCrawler->attr('parentId');
                    foreach ($nodeCrawler as $node) {
                        $resultArray[$node->nodeName] = $node->nodeValue;
                    }
                    return $resultArray;
                });
            foreach ($externalCategoriesInfo as $externalCategory) {
                $oldExternalCategory = $em
                    ->getRepository('AppBundle:ExternalCategory')
                    ->findOneBy(array(
                        'externalId' => $externalCategory,
                        'site' => $siteId,
                    ));
                if (!$externalCategory['parentId']) {
                    $externalCategory['parentId'] = 0;
                }
                if (!$oldExternalCategory) {
                    $newExternalCategory = new ExternalCategory();
                } else {
                    $newExternalCategory = $oldExternalCategory;
                }
                $newExternalCategory->setVersion($newVersion);
                $newExternalCategory->setExternalId($externalCategory['externalId']);
                $newExternalCategory->setName($externalCategory['category']);
                $newExternalCategory->setSite($site);
                $newExternalCategory->setParentId($externalCategory['parentId']);
                $em->persist($newExternalCategory);
                $em->flush();
            }
            $output->writeln($delimer . 'Categories from XML - ' . count($externalCategoriesInfo) . $delimer);
            $output->writeln($delimer . 'End parse categories. Memory usage: ' . (memory_get_usage() / 1024) . ' KB' . $delimer);

            $output->writeln($delimer . 'Start parse offers. Memory usage: ' . (memory_get_usage() / 1024) . ' KB' . $delimer);
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
            $i = 0;
            foreach ($productsInfo as $product) {
                if ($i % 1000 == 0) {
                    $em->flush();
                    $em->clear('AppBundle\Entity\Product');
                    $output->writeln($delimer . 'Offers - ' . $i . '. Memory usage: ' . (memory_get_usage() / 1024) . ' KB' . $delimer);
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
                        $newVendor = new Vendor();
                    }
                    $newVendor->setVersion($newVersion);
                    if (isset($product['vendorCode'])) {
                        $newVendor->setCode($product['vendorCode']);
                    }
                    $newVendor->setSite($site);
                    $newVendor->setName($product['vendor']);
                    $em->persist($newVendor);
                    $em->flush();
                    $newProduct->setVendor($newVendor);
                }
                $em->persist($newProduct);
                $em->flush();
                $i++;
            }
            $output->writeln($delimer . 'End parse offers. Memory usage: ' . (memory_get_usage() / 1024) . ' KB' . $delimer);
            if (isset($noCategoryArray)) {
                $output->writeln($delimer . 'No categories - ' . count($noCategoryArray) . $delimer);
            }
            $output->writeln($delimer . 'Imported offers - ' . count($productsInfo) . $delimer);
            $output->writeln($delimer . 'End parse market ' . $siteId . '. Memory usage: ' . (memory_get_usage() / 1024) . ' KB' . $delimer);
        }

    }
}