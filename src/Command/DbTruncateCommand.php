<?php

namespace App\Command;


use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

class DbTruncateCommand extends Command
{
    protected static $defaultName = 'db:truncate';

    const HELP = <<<HELPTEXT
syntax: db:truncate [--exclude=table1...tableN] dbname
where 
   dbname - is the name of the database.  {eg : setup, sales, model, competition...}  
   table1...tableN - tables to NOT truncate.
HELPTEXT;

    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct('DbTruncateCommand');
        $this->kernel=$kernel;
    }

    /**
     * @param $name
     * @return EntityManagerInterface
     */
    private function getEntityManager($name) : EntityManagerInterface
    {
        $emName = 'doctrine.orm.'.$name.'_entity_manager';
        /** @var EntityManagerInterface $em */
        $em = $this->kernel->getContainer()->get($emName);
        return $em;
    }



    protected function configure()
    {
        $this
            ->setDescription('Truncate a database.')
            ->addArgument('dbname', InputArgument::REQUIRED, 'Name of database [REQUIRED]')
            ->addOption('exclude', null, InputOption::VALUE_OPTIONAL, 'Name of tables to exclude')
            ->setHelp('syntax: database:truncate [--exclude=table1,tableN] $dbname');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $dbname = $input->getArgument('dbname');
        if(is_null($dbname)) {
            $io->error('A dbname is not specified.');
            return;
        }
        $exclude = $input->hasOption('exclude')? $input->getOption('exclude'):false;
        try{
            $em = $this->getEntityManager($dbname);
            $purger = new ORMPurger($em,explode(',',$exclude));
            $connection=$purger->getObjectManager()->getConnection();
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $purger->purge();
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
        } catch(\Exception $e) {
            $message = $e->getMessage();
            $io->note($message);
        }
        $io->success(sprintf("Database %s has been successfully truncated.",$dbname));
    }
}
