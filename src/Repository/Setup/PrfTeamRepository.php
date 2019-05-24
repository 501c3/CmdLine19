<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/14/19
 * Time: 6:20 PM
 */

namespace App\Repository\Setup;

use App\Entity\Setup\PrfClass;
use App\Entity\Setup\PrfTeam;
use App\Entity\Setup\Tss;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class PrfTeamRepository extends ServiceEntityRepository
{
    /** @var \Doctrine\ORM\Query  */
    private $querySoloTeam;
    /** @var \Doctrine\ORM\Query  */
    private $queryCoupleTeam;
    /** @var \Doctrine\ORM\Query  */
    private $queryTeamClass;
    /** @var \Doctrine\ORM\Query  */
    private $queryTeamClassTss;


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrfTeam::class);

        $qbSoloTeam = $this->createQueryBuilder('team');
        $qbSoloTeam->select('team')
                        ->where("JSON_LENGTH(team.proficiencies,'$')=1")
                        ->andWhere("JSON_EXTRACT(team.proficiencies,'$[0]')=:teamProficiency");
        $this->querySoloTeam=$qbSoloTeam->getQuery();

        $qbCoupleTeam = $this->createQueryBuilder('team');
        $qbCoupleTeam->select('team')
                    ->Where("JSON_LENGTH(team.proficiencies,'$')=2")
                    ->andWhere("JSON_EXTRACT(team.proficiencies,'$[0]')=:teamProficiency")
                    ->andWhere("JSON_EXTRACT(team.proficiencies,'$[1]')=:partnerProficiency");
        $this->queryCoupleTeam = $qbCoupleTeam->getQuery();

        $qbTeamClass = $this->createQueryBuilder('team');
        $qbTeamClass->select('team','class')
                    ->innerJoin('team.prfClass','class')
                    ->where('team=:team')
                    ->andWhere('class=:class');
        $this->queryTeamClass = $qbTeamClass->getQuery();

        $qbTeamClassTss = $this->createQueryBuilder('team');
        $qbTeamClassTss->select('team','class','tss')
                        ->innerJoin('team.prfClass','class')
                        ->innerJoin('team.tss','tss')
                        ->where('team=:team')
                        ->andWhere('class=:class')
                        ->andWhere('tss=:tss');
        $this->queryTeamClassTss = $qbTeamClassTss->getQuery();
    }

    /**
     * @param Tss $tss
     * @param PrfClass $class
     * @param array $partnerProficiencies
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createTeams(Tss $tss, PrfClass $class, array $partnerProficiencies) : array
    {
        $teams = [];
        $teamProficiency = $class->getName();
        switch(count($partnerProficiencies)) {
            case 0:
                $this->querySoloTeam->setParameter('teamProficiency',$teamProficiency);
                /** @var PrfTeam $team */
                $team = $this->querySoloTeam->getOneOrNullResult();
                if($team){
                    $teams[]=$this->fetchWith($tss,$class,$team);
                } else {
                    $team = new PrfTeam();
                    $team->addTss($tss)
                        ->addPrfClass($class)
                        ->setProficiencies([$teamProficiency]);
                    $teams[]=$team;
                }
                break;
            default:
                foreach($partnerProficiencies as $partnerProficiency)
                {
                    $this->queryCoupleTeam->setParameters(
                        [':teamProficiency'=>$teamProficiency,':partnerProficiency'=>$partnerProficiency]);

                    $team = $this->queryCoupleTeam->getOneOrNullResult();
                    if($team){
                        $teams[]=$this->fetchWith($tss,$class,$team);
                    } else {
                        $team = new PrfTeam();
                        $team->addTss($tss)
                            ->addPrfClass($class)
                            ->setProficiencies([$teamProficiency,$partnerProficiency]);
                        $this->_em->persist($team);
                        $teams[]=$team;
                    }
                }
        }
        $this->_em->flush();
        return $teams;
    }

    /**
     * @param PrfTeam $team
     * @param PrfClass $class
     * @param Tss $tss
     * @return PrfTeam|mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function fetchWith(Tss $tss, PrfClass $class, PrfTeam $team): PrfTeam
    {
        $parameters=['team'=>$team,'class'=>$class];
        $parametersTss=['team'=>$team,'class'=>$class,'tss'=>$tss];
        $teamClassQuery = $this->queryTeamClass->setParameters($parameters);
        $teamClassTssQuery = $this->queryTeamClassTss->setParameters($parametersTss);
        $teamClass=$teamClassQuery->getOneOrNullResult();
        if($teamClass) {
            $teamClassTss=$teamClassTssQuery->getOneOrNullResult();
            if($teamClassTss) {
                return $teamClassTss;
            } else {
                $teamClass->addTss($tss);
                return $teamClass;
            }
        } else {
            $team->addPrfClass($class)
                 ->addTss($tss);
            return $team;
        }
    }

}