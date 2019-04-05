<?php
/**
 * Copyright (c) 2019. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/8/19
 * Time: 4:18 PM
 */

namespace App\Common;

use App\Entity\Sales\Channel;
use App\Entity\Sales\Pricing;
use App\Repository\Sales\ChannelRepository;
use Doctrine\ORM\EntityManagerInterface;


class YamlDbSales
{
    const EXPECTED_KEYS=['comment','channel','competition','logo','venue','city','state','date','monitor','inventory','processor'];
    const INVENTORY_TAGS=['participant','extra','discount','penalty'];



    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    private $heading = [];

    private $inventory = [];

    private $processors = [];

    private $file;

    /**
     * YamlDbSales constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $file
     * @throws AppParseException
     * @throws \Exception
     */
    public function parseSales(string $file)
    {
        $this->file = $file;
        $parsed = YamlPosition::yamlAddPosition($file);
        $keyedData = [];
        $positionKeys = [];
        foreach ($parsed as $keyPosition => $dataPosition) {
            list($key, $position) = explode('|', $keyPosition);
            if (!in_array($key, self::EXPECTED_KEYS)) {
                throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                    [$file, $key, $position, self::EXPECTED_KEYS]);
            }
            $keyedData[$key] = $dataPosition;
            $positionKeys[] = $position;
        }
        $diff = array_diff(self::EXPECTED_KEYS, array_keys($keyedData));
        if (count($diff)) {
            throw new AppParseException(AppExceptionCodes::MISSING_KEYS,
                [$file, $diff, $positionKeys]);
        }
        foreach($keyedData as $key=>$info) {
            if($key!=='comment'){
                $method='parse'.ucfirst($key);
                $this->$method($info);
            }
        }
        $this->build();
    }

    /**
     * @param string $info
     */
    protected function parseChannel(string $info)
    {
        list($data,$position) = explode('|', $info);
        $this->heading['channel']=['name'=>$data,'position'=>$position];
    }

    /**
     * @param string $info
     */
    protected function parseCompetition(string $info)
    {
        list($data,$position) = explode('|',$info);
        $this->heading['competition']=['name'=>$data,'position'=>$position];
    }

    /**
     * @param string $info
     * @throws AppParseException
     */
    protected function parseLogo(string $info)
    {
        list($file,$position) = explode('|',$info);
        $realpath = realpath(__DIR__.'/../../assets/'.$file);
        if (!file_exists($realpath)) {
            throw new AppParseException(AppExceptionCodes::FILE_NOT_FOUND,
                [$this->file,null,$position]);
        }
        $contents = file_get_contents($realpath);
        $imageFileType=strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $blob=base64_encode($contents);
        $image='data:image/'.$imageFileType.';base64,'.$blob;
        $this->heading['logo']=['image'=>$image,'position'=>$position];
    }

    /**
     * @param string $info
     */
    protected function parseVenue(string $info)
    {
        list($data,$position) = explode('|',$info);
        $this->heading['venue']=['name'=>$data,'position'=>$position];
    }

    /**
     * @param $info
     */
    protected function parseCity($info)
    {
        list($data,$position) = explode('|',$info);
        $this->heading['city']=['name'=>$data,'position'=>$position];
    }

    /**
     * @param $info
     */
    protected function parseState($info)
    {
        list($data,$position) = explode('|',$info);
        $this->heading['state']=['name'=>$data,'position'=>$position];
    }

    /**
     * @param string $str
     * @param $position
     * @throws AppParseException
     */
    private function checkDate(string $str, $position)
    {
        try {
            list($year,$month,$day) = explode('-',$str);
            checkdate($month, $day, $year);
        } catch (\Exception $e) {
            throw new AppParseException(AppExceptionCodes::INVALID_PARAMETER,
                [$this->file, $str, $position]);
        }
    }

    /**
     * @param array $info
     * @throws AppParseException
     * @throws \Exception
     */
    protected function parseDate(array $info)
    {
        $count = 0; $dates = [];
        foreach($info as $keyPosition=>$dataPosition) {
            list($key,$position) = explode('|', $keyPosition);
            $expectedKeys = ['start','finish'];
            if(!in_array($key,$expectedKeys)) {
                throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                    [$this->file,$key,$position,$expectedKeys]);
            }
            list($date,$position) = explode('|',$dataPosition);
            $this->checkDate($date,$position);
            $dates[$key]=['date'=>new \DateTime($date),'position'=>$position];
            $count++;
        }
        /** @var \DateTime $startDate */
        $startDate = $dates['start']['date'];
        /** @var \DateTime $finishDate */
        $finishDate = $dates['finish']['date'];
        $finishPosition = $dates['finish']['position'];
        $this->heading['date']=['start'=>$startDate,'finish'=>$finishDate];
        $difference = $startDate->diff($finishDate);
        /* If difference is negative then flag error */
        if($difference->invert){
            throw new AppParseException(AppExceptionCodes::BAD_DATE_ORDER,
                [$this->file, $finishDate->format('Y-m-d'), $finishPosition]);
        }
    }

    /**
     * @param $info
     * @throws AppParseException
     */
    protected function parseMonitor($info)
    {

        $this->heading['monitor']=[];
        foreach($info as $monitorPosition=>$emailPosition) {
            list($monitor,$positionMon) = explode('|',$monitorPosition);
            list($email,$positionEmail) = explode('|',$emailPosition);
            if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
                throw new  AppParseException(AppExceptionCodes::INVALID_EMAIL,
                    [$this->file,$email,$positionEmail]);
            }

            $this->heading['monitor'][]=['monitor'=>['name'=>$monitor,'position'=>$positionMon],
                                         'email'=>['address'=>$email,'position'=>$positionEmail]];
        }
    }


    /**
     * @param $info
     * @throws AppParseException
     */
    protected function parseInventory($info)
    {
        foreach ($info as $datePosition => $inventoryDataPosition) {
            list($date, $position) = explode('|', $datePosition);
            $this->checkDate($date,$position);
            $this->inventory[$date]=[];
            foreach($inventoryDataPosition as $categoryPosition=>$inventoryPosition){
                list($category,$position) = explode('|',$categoryPosition);
                if(!in_array($category, self::INVENTORY_TAGS)) {
                    throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                        [$this->file,$category,$position,self::INVENTORY_TAGS]);
                }
                $this->inventory[$date][$category]=[];
                foreach($inventoryPosition as $itemPosition=>$pricePosition){
                    list($item,) = explode('|',$itemPosition);
                    list($price,$position) = explode('|',$pricePosition);
                    if(!is_numeric($price)) {
                        throw new AppParseException(AppExceptionCodes::INVALID_PARAMETER,
                            [$this->file,$price,$position]);
                    }
                    $this->inventory[$date][$category][$item]=$price;
                }
            }
        }
    }

    /**
     * @param $info
     * @throws AppParseException
     * @throws \Exception
     */
    protected function parseProcessor($info)
    {
       foreach($info as $processorPosition=>$dataPosition){
           list($processor,)=explode('|',$processorPosition);
           $this->processors[$processor]=[];
           foreach($dataPosition as $modePosition=>$varsPositions){
               list($mode,$position) = explode('|',$modePosition);
               $expectedModes = ['test','prod'];
               if(!in_array($mode,$expectedModes)) {
                   throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                       [$this->file, $mode, $position, $expectedModes]);
               }
               $this->processors[$processor][$mode]=YamlPosition::isolate($varsPositions);
           }
       }
    }

    /**
     * @throws \Exception
     */
    protected function build()
    {
        $channel=$this->buildChannel($this->entityManager);
        $repository = $this->entityManager->getRepository(Pricing::class);
        $repository->clearPricing($channel);
        foreach($this->inventory as $date=>$inventory) {
            $repository->addPricing($channel,$date,$inventory);
        }
    }

    /**
     * @param EntityManagerInterface $em
     * @return Channel
     * @throws \Exception
     */
    private function buildChannel(EntityManagerInterface $em)
    {
        /** @var ChannelRepository $repository */
        $repository = $em->getRepository(Channel::class);
        /** @var Channel $channel */
        $channel = $repository->configureChannel($this->heading);
        return $channel;
    }
}