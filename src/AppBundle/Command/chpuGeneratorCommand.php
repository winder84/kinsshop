<?php
namespace AppBundle\Command;

use AppBundle\Entity\Vendor;
use AppBundle\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class chpuGeneratorCommand extends ContainerAwareCommand
{
    protected $output;

    protected $delimer = '----------';

    protected $em;

    protected function configure()
    {
        $this
            ->setName('kins:chpu')
            ->setDescription('Parse markets')
//            ->addArgument(
//                'marketId',
//                InputArgument::OPTIONAL,
//                'Who do you want to parse?'
//            )
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
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->output = $output;
        $this->outputWriteLn('Start generate chpu - categories');
        $categories = $this->em
                ->getRepository('AppBundle:Category')
                ->findAll();
        foreach ($categories as $category) {
            $categoryAlias = $category->getAlias();
            if (empty($categoryAlias)) {
                $name = $category->getName();
                $alias = $this->TransUrl($name);
                $category->setAlias($alias);
                $this->em->persist($category);
            }
        }
        $this->em->flush();
        $this->em->clear('AppBundle\Entity\Category');
        $this->outputWriteLn('End generate chpu - categories');

        $this->outputWriteLn('Start generate chpu - vendors');
        $vendors = $this->em
                ->getRepository('AppBundle:Vendor')
                ->findAll();
        foreach ($vendors as $vendor) {
            $vendorAlias = $vendor->getAlias();
            if (empty($vendorAlias)) {
                $name = $vendor->getName();
                $alias = $this->TransUrl($name);
                $vendor->setAlias($alias);
                $this->em->persist($vendor);
            }
        }
        $this->em->flush();
        $this->em->clear('AppBundle\Entity\Vendor');
        $this->outputWriteLn('End generate chpu - vendors');

        $this->outputWriteLn('Start generate chpu - products');
        $iterableResult = $this->em->createQuery("SELECT p FROM 'AppBundle\Entity\Product' p WHERE p.isDelete = 0")->iterate();
        $i = 0;
        while ((list($product) = $iterableResult->next()) !== false) {
            $productAlias = $product->getAlias();
            if (empty($productAlias)) {
                $name = $product->getExternalId() . '_' . $product->getName();
                $alias = $this->TransUrl($name);
                $product->setAlias($alias);
                $this->em->persist($product);
            }
            if ($i % 10000 == 0) {
                $this->em->flush();
                $this->em->clear('AppBundle\Entity\Product');
                $this->em->detach($product);
                $this->outputWriteLn('Offers - ' . $i . '.');
            }

            $i++;
        }
        $this->em->flush();
        $this->em->clear('AppBundle\Entity\Product');
        $this->outputWriteLn('Offers - ' . $i . '.');
        $this->outputWriteLn('End generate chpu - products');

    }

    private function outputWriteLn($text)
    {
        $newTimeDate = new \DateTime();
        $newTimeDate = $newTimeDate->format(\DateTime::ATOM);
        $this->output->writeln($this->delimer. $newTimeDate . ' ' . $text . ' Memory usage: ' . round(memory_get_usage() / (1024 * 1024)) . ' MB' . $this->delimer);
    }

    private function TransUrl($str)
    {
        $tr = array(
            "А"=>"a",
            "Б"=>"b",
            "В"=>"v",
            "Г"=>"g",
            "Д"=>"d",
            "Е"=>"e",
            "Ё"=>"e",
            "Ж"=>"j",
            "З"=>"z",
            "И"=>"i",
            "Й"=>"y",
            "К"=>"k",
            "Л"=>"l",
            "М"=>"m",
            "Н"=>"n",
            "О"=>"o",
            "П"=>"p",
            "Р"=>"r",
            "С"=>"s",
            "Т"=>"t",
            "У"=>"u",
            "Ф"=>"f",
            "Х"=>"h",
            "Ц"=>"ts",
            "Ч"=>"ch",
            "Ш"=>"sh",
            "Щ"=>"sch",
            "Ъ"=>"",
            "Ы"=>"i",
            "Ь"=>"j",
            "Э"=>"e",
            "Ю"=>"yu",
            "Я"=>"ya",
            "а"=>"a",
            "б"=>"b",
            "в"=>"v",
            "г"=>"g",
            "д"=>"d",
            "е"=>"e",
            "ё"=>"e",
            "ж"=>"j",
            "з"=>"z",
            "и"=>"i",
            "й"=>"y",
            "к"=>"k",
            "л"=>"l",
            "м"=>"m",
            "н"=>"n",
            "о"=>"o",
            "п"=>"p",
            "р"=>"r",
            "с"=>"s",
            "т"=>"t",
            "у"=>"u",
            "ф"=>"f",
            "х"=>"h",
            "ц"=>"ts",
            "ч"=>"ch",
            "ш"=>"sh",
            "щ"=>"sch",
            "ъ"=>"y",
            "ы"=>"i",
            "ь"=>"j",
            "э"=>"e",
            "ю"=>"yu",
            "я"=>"ya",
            " "=> "_",
            "."=> "",
            "/"=> "_",
            ","=>"_",
            "-"=>"_",
            "("=>"",
            ")"=>"",
            "["=>"",
            "]"=>"",
            "="=>"_",
            "+"=>"_",
            "*"=>"",
            "?"=>"",
            "\""=>"",
            "'"=>"",
            "&"=>"",
            "%"=>"",
            "#"=>"",
            "@"=>"",
            "!"=>"",
            ";"=>"",
            "№"=>"",
            "^"=>"",
            ":"=>"",
            "~"=>"",
            "\\"=>""
        );
        return strtr($str,$tr);
    }

}