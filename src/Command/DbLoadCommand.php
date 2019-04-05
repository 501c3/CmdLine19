<?php

namespace App\Command;

use App\Signal\ProcessEvent;
use App\Signal\ProcessStatus;
use App\Signal\ProcessSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\KernelInterface;

class DbLoadCommand extends Command
{
    protected static $defaultName = 'db:load';

    /** @var KernelInterface  */
    private $kernel;

    /** @var EntityManagerInterface */
    private $em;

    private $dump = [];

    /** @var EventDispatcher */
    private $dispatcher;

    /** @var ProcessSubscriber */
    private $subscriber;

    private $count;

    const HELP = <<<HELPTEXT
syntax: db:load dbname dump
  where dbname is the name of a database.
        dump is the name of a directory or file.
HELPTEXT;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct('DbLoadCommand');
        $this->kernel = $kernel;
        $this->subscriber = new ProcessSubscriber();
        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber($this->subscriber);

    }

    private function getEntityManager($dbname) : EntityManagerInterface
    {
        $emName = 'doctrine.orm.'.$dbname.'_entity_manager';
        /** @var EntityManagerInterface $em */
        $em = $this->kernel->getContainer()->get($emName);
        return $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Loads database from dump.')
            ->addArgument('dbname', InputArgument::REQUIRED,
                'database to restore.')
            ->addArgument('dump',InputArgument::REQUIRED,
                'dump directory or file to load.')
            ->setHelp(self::HELP);
    }

    private function locateDb(string $dbname)
    {
        $em = $this->getEntityManager($dbname);
        $this->em = $em;
        return $em?true:false;
    }

    private function locateDump($pathfile)
    {

        if(is_dir($pathfile))  {
            $predump=scandir($pathfile);
            array_shift($predump);
            array_shift($predump);
            $this->count = count($predump);
            foreach($predump as $file){
                $parts = pathinfo($file);
                if($parts['extension']=='sql'){
                    $file=$pathfile.'/'.$parts['filename'].'.'.$parts['extension'];
                    $this->dump[]=$file;
                }
            }
        }
        if(is_file($pathfile)) {
            $parts = pathinfo($pathfile);
            if($parts['extension']=='sql'){
                $this->dump=[$pathfile];
            }
        }
        $this->count =count($this->dump);
        return $this->count?true:false;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        /** @var ProcessSubscriber $subscriber */
        $this->subscriber->setOutputInterface($output);
        $dbname = $input->getArgument('dbname');
        $dump = $input->getArgument('dump');
        $count = 0;
        if($this->locateDb($dbname)) {
            if($this->locateDump($dump)) {
                $event = new ProcessEvent(new ProcessStatus(ProcessStatus::COMMENCE,$this->count));
                $this->dispatcher->dispatch('process.update', $event);
                $conn = $this->em->getConnection();
                $conn->query('SET FOREIGN_KEY_CHECKS = 0');
                foreach($this->dump as $file) {
                    $sql = file_get_contents($file);
                    $conn->query($sql);
                    $event = new ProcessEvent(new ProcessStatus(ProcessStatus::WORKING, ++$count));
                    $this->dispatcher->dispatch('process.update',$event);
                }
                $conn->query('SET FOREIGN_KEY_CHECKS = 1');
                $event = new ProcessEvent(new ProcessStatus(ProcessStatus::COMPLETE,$this->count));
                $this->dispatcher->dispatch('process.update', $event);
            } else {
                $io->error(sprintf('Data dump: %s was not located. Check path.',$dump));
            }
        } else {
            $io->error(sprintf('Database: %s was not located.',$dbname));
        }
    }
}
