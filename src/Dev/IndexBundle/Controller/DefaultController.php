<?php

namespace Dev\IndexBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/ ")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/weather", name="get_weather")
     */
    public function weatherAction (Request $request, $col = 3)
    {


    $day_of_the_week_array = array(1 => 'понедельник',
                                   2 => 'вторник',
                                   3 => 'среда',
                                   4 => 'четверг',
                                   5 => 'пятница',
                                   6 => 'суббота',
                                   7 => 'воскресение');

    $time_of_day = array(0 => 'утро',
                         1 => 'день',
                         2 => 'вечер',
                         3 => 'ночь');

    // Загружаем файл прогноза погоды для выбранного города
    $data_file = 'http://export.yandex.ru/weather-ng/forecasts/'.$request->request->get('city').'.xml';

    if(!$data_file){
         return new Response(json_enconde("some"),500);
    }
    // загружаем xml файл через simple_xml
    $xml = simplexml_load_file($data_file);

    $out = array(); // Массив вывода прогноза
    $counter = 0 ; // Счетчик количества дней, для которых доступен прогноз


    foreach ( $xml->day as $day )  {

        if ($counter == $col) {break;}

        $get_date = explode ("-" , $day['date']) ;
        $day_of_week = date("N", mktime(0, 0, 0, $get_date[1], $get_date[2], $get_date[0])) ;

        $out[$counter]['day'] = $get_date[2] ;
        $out[$counter]['month'] = $get_date[1] ;
        $out[$counter]['year'] = $get_date[0] ;
        $out[$counter]['day_of_week'] = $day_of_the_week_array[$day_of_week] ;

        for ($i=0;$i<=3;$i++) {

            if($day->day_part[$i]->temperature == '') {

                $get_temp_from =  $day->day_part[$i]->temperature_from;
                $get_temp_to =  $day->day_part[$i]->temperature_to;

            }

            if($get_temp_from>0 ) {$get_temp_from = '+'.$get_temp_from ; }
            if($get_temp_to>0 ) {$get_temp_to = '+'.$get_temp_to ; }
            $out[$counter]['weather'][$i]['temp_from'] = $get_temp_from;
            $out[$counter]['weather'][$i]['temp_to'] = $get_temp_to;
            $out[$counter]['weather'][$i]['image'] = $day->day_part[$i]->{'image-v3'};
            $out[$counter]['weather'][$i]['time_of_day'] = $time_of_day[$i] ;


        } $counter++ ;
    }

            for ( $i = 0 ; $i < count($out); $i++){
                $rendered[] = $this->renderView("DevIndexBundle:Default:widget.html.twig", array("out"=>$out[$i]));
            }


        return new Response(json_encode($rendered),200);


        return $out;




    }




}
