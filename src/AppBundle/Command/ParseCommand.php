<?php
namespace AppBundle\Command;

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
        if ($marketId) {
        } else {
            $em = $this->getContainer()->get('doctrine')->getManager();
            $sites = $em
                ->getRepository('AppBundle:Site')
                ->findAll();

            foreach ($sites as $site) {
                $nowDate = new \DateTime('NOW');
                $output->writeln($nowDate->format(\DateTime::ATOM) . ' start parse market ' . $site->getId());
                $lastParseDate = $site->getLastParseDate();
                //            if ($lastParseDate->diff($nowDate)->format('%h') < $site->getUpdatePeriod()) {
                //                continue;
                //            }

                $output->writeln($nowDate->format(\DateTime::ATOM) . ' start download xml');
//                $xmlContent = file_get_contents($site->getXmlParseUrl());
                $xmlContent = file_get_contents($this->getContainer()->get('kernel')->getRootDir() . '/../web/akusherstvo_products_20150909_210106.xml');
                $output->writeln($nowDate->format(\DateTime::ATOM) . ' end download xml');

                $crawler = new Crawler($xmlContent);
                $site->setLastParseDate(new \DateTime());
                $em->flush();

                $output->writeln($nowDate->format(\DateTime::ATOM) . ' start parse categories');
                $categoriesInfo = $crawler
                    ->filterXPath('//categories/category')
                    ->each(function (Crawler $nodeCrawler) {
                        $resultArray['externalId'] = $nodeCrawler->attr('id');
                        $resultArray['parentId'] = $nodeCrawler->attr('parentId');
                        foreach ($nodeCrawler as $node) {
                            $resultArray[$node->nodeName] = $node->nodeValue;
                        }
                        return $resultArray;
                    });
                $output->writeln($nowDate->format(\DateTime::ATOM) . ' end parse categories');

                $output->writeln($nowDate->format(\DateTime::ATOM) . ' start parse offers');
                $productsInfo = $crawler
                    ->filterXPath('//offers/offer')
                    ->each(function (Crawler $nodeCrawler) {
                        $children = $nodeCrawler->children();
                        $resultArray['externalId'] = $nodeCrawler->attr('id');
                        foreach ($children as $child) {
                            $resultArray[$child->nodeName] = $child->nodeValue;
                        }
                        return $resultArray;
                    });
                $output->writeln($nowDate->format(\DateTime::ATOM) . ' end parse offers');
                $output->writeln($nowDate->format(\DateTime::ATOM) . ' end parse market ' . $site->getId());
            }
            var_dump($categoriesInfo[0]);
            var_dump($productsInfo[0]);
        }

    }
}