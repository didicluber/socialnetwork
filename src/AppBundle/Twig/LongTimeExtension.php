<?php

namespace AppBundle\Twig;


class LongTimeExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('long_time', array($this, 'LongTimeFilter'))
        );
    }

    public function getName()
    {
        return 'long_time_extension';
    }

    /**
     * Método devuelve tiempo transcurrido
     *
     * @param $date
     * @return string
     */
    public function LongTimeFilter($date) {
        if ($date == null) {
            return "Sans date";
        }

        $start_date = $date;
        $since_start = $start_date->diff(new \DateTime(date("d-m-Y") . " " . date("H:i:s")));

        if ($since_start->y == 0) {
            if ($since_start->m == 0) {
                if ($since_start->d == 0) {
                    if ($since_start->h == 0) {
                        if ($since_start->i == 0) {
                            if ($since_start->s == 0) {
                                $result = $since_start->s . ' secondes';
                            } else {
                                if ($since_start->s == 1) {
                                    $result = $since_start->s . ' seconde';
                                } else {
                                    $result = $since_start->s . ' secondes';
                                }
                            }
                        } else {
                            if ($since_start->i == 1) {
                                $result = $since_start->i . ' minute';
                            } else {
                                $result = $since_start->i . ' minutes';
                            }
                        }
                    } else {
                        if ($since_start->h == 1) {
                            $result = $since_start->h . ' heure';
                        } else {
                            $result = $since_start->h . ' heures';
                        }
                    }
                } else {
                    if ($since_start->d == 1) {
                        $result = $since_start->d . ' jour';
                    } else {
                        $result = $since_start->d . ' jours';
                    }
                }
            } else {
                if ($since_start->m == 1) {
                    $result = $since_start->m . ' mois';
                } else {
                    $result = $since_start->m . ' mois';
                }
            }
        } else {
            if ($since_start->y == 1) {
                $result = $since_start->y . ' année';
            } else {
                $result = $since_start->y . ' années';
            }
        }

        return "Il y a " . $result;
    }
}