<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/14/19
 * Time: 6:20 PM
 */

namespace App\Repository\Setup;

use App\Entity\Setup\AgeClass;
use App\Entity\Setup\AgeTeam;
use App\Entity\Setup\Tss;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class AgeTeamRepository extends ServiceEntityRepository
{
    /** @var \Doctrine\ORM\Query  */
    private $querySolo;

    /** @var \Doctrine\ORM\Query  */
    private $queryCouple;

    /** @var \Doctrine\ORM\Query  */
    private $queryTeamClass;

    /** @var \Doctrine\ORM\Query  */
    private $queryTeamClassTss;



    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgeTeam::class);

        $qbSolo = $this->createQueryBuilder('team');
        $qbSolo->select('team')
               ->where("JSON_LENGTH(team.years,'$')=1")
               ->andWhere("JSON_EXTRACT(team.years,'$[0]')=:year");
        $this->querySolo=$qbSolo->getQuery();


        $qbCouple = $this->createQueryBuilder('team');
        $qbCouple->select('team')
                ->where("JSON_LENGTH(team.years,'$')=2")
                ->andWhere("JSON_EXTRACT(team.years,'$[0]')=:yearsOlder")
                ->andWhere("JSON_EXTRACT(team.years,'$[1]')=:yearsYounger");
        $this->queryCouple = $qbCouple->getQuery();

        $qbTeamClass = $this->createQueryBuilder('team');
        $qbTeamClass->select('team','class')
                    ->innerJoin('team.ageClass','class')
                    ->where('team=:team')
                    ->andWhere('class=:class');
        $this->queryTeamClass=$qbTeamClass->getQuery();

        $qbTeamClassTss = $this->createQueryBuilder('team');
        $qbTeamClassTss->select('team','class','tss')
                        ->innerJoin('team.ageClass','class')
                        ->innerJoin('team.tss','tss')
                        ->where('team=:team')
                        ->andWhere('class=:class')
                        ->andWhere('tss=:tss');
        $this->queryTeamClassTss=$qbTeamClassTss->getQuery();

    }

    /**
     * @param Tss $tss
     * @param AgeClass $ageClass
     * @param array $ageBounds
     * @return TYPE_NAME|array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException When listing team age combinations oldest age is listed first
     * Examples: [45,23] ...45 years dancing with person 23 years
     */
    public function createTeams(Tss $tss, AgeClass $ageClass, ...$ageBounds): array
    {
        $teams = [];
        switch(count($ageBounds)) {
            case 1:
                for($year=$ageBounds[0][0];$year<=$ageBounds[0][1];$year++) {
                    $this->querySolo->setParameters([':year'=>$year]);
                    $team = $this->querySolo->getOneOrNullResult();
                    if($team) {
                        $teams[]=$this->fetchWith($tss,$ageClass,$team);
                    } else {
                        $team = new AgeTeam();
                        $team->setYears([$year])
                            ->addAgeClass($ageClass)
                            ->addTss($tss);
                        $this->_em->persist($team);
                        $teams[]=$team;
                    }
                }
                break;
            case 2:
                $ageCombinations = [];
                foreach(range($ageBounds[0][0],$ageBounds[0][1]) as $a) {
                    foreach(range($ageBounds[1][0],$ageBounds[1][1]) as $b) {
                        $partners = $a>$b?[$a,$b]:[$b,$a];
                        if(!in_array($partners,$ageCombinations)) {
                            $ageCombinations[]=$partners;
                        }
                    }
                }
                foreach($ageCombinations as $coupleAges) {
                    $parameters = ['yearsOlder'=>$coupleAges[0],'yearsYounger'=>$coupleAges[1]];
                    $this->queryCouple->setParameters($parameters);
                    $team = $this->queryCouple->getOneOrNullResult();
                    if($team) {
                        $teams[] = $this->fetchWith($tss, $ageClass, $team);
                    } else {
                        $team = new AgeTeam();
                        $team->addAgeClass($ageClass)
                            ->addTss($tss)
                            ->setYears($coupleAges);
                        $this->_em->persist($team);
                        $teams[] = $team;
                    }
                }
        }
        $this->_em->flush();
        return $teams;
    }

    /**
     * @param Tss $tss
     * @param AgeClass $class
     * @param AgeTeam $team
     * @return AgeTeam|mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function fetchWith(Tss $tss, AgeClass $class, AgeTeam $team) : AgeTeam
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
            $team->addAgeClass($class)
                ->addTss($tss);
            return $team;
        }
    }
}