<?php

namespace App\Models;

use CodeIgniter\Model;

class SchedulesWeeksModel extends Model
{
    function formatData($weekSchedulesData)
    {
        $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
        $weekSchedule = array();

        foreach ($days as $day) {
            $result = (object)
            $daySchedule = array();
            $result->day = $day;

            foreach ($weekSchedulesData as $schedule) {
                if ($day == $schedule['day']) {
                    $result->date = $schedule['date_day'];
                    $result->dateWeekStart = $schedule['date_week_start'];
                    $result->dateWeekEnd = $schedule['date_week_end'];

                    if ($schedule['class_id']) {
                        array_push($daySchedule, [
                            'class_id' => $schedule['class_id'],
                            'id' => uniqid(),
                            "schedules_weeks_id" => $schedule['schedules_weeks_id'],
                            "hour" => $schedule['hour'],
                            "link" => $schedule['link'],
                            "name" => $schedule['class_name'],
                            "description" => $schedule['class_description'],
                            "level" => $schedule['class_level'],
                            "online_price" => $schedule['online_price'],
                            "offline_price" => $schedule['offline_price']
                        ]);
                    }
                }
            }

            $result->schedule = $daySchedule;
            array_push($weekSchedule, $result);
        }
        return $weekSchedule;
    }

    function updateClassLink($scheduleWeekId, $link)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('schedules_weeks');

        $builder->set('link', $link)->where('id', $scheduleWeekId)->update();
    }

    function getWeekSchedule($startDate, $endDate)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('schedules_weeks_view');

        return $this->formatData($builder->orderBy('date_day', 'asc')->where('date_day >=', $startDate)->where('date_day <=', $endDate)->get()->getResultArray());
    }

    function getMostRecentSchedule()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('most_recent_schedule');

        return $this->formatData($builder->get()->getResultArray());
    }


    function getDaySchedule($date)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('schedules_weeks_view');

        return $builder->where('date_day =', $date)->get()->getResultArray();
    }

    function deleteSchedule($startDate)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('schedules_weeks');
        $builder->delete(['date_week_start' => $startDate]);
    }


    function insertWeekSchedule($weekSchedule, $startDate, $endDate)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('schedules_weeks');

        foreach ($weekSchedule as $daySchedule) {

            if (count($daySchedule['schedule']) == 0) {
                $builder->insert([
                    'hour' => null,
                    'class_id' => null,
                    'day' => $daySchedule['day'],
                    'date_day' => $daySchedule['date_day'],
                    'date_week_start' => $startDate,
                    'date_week_end' => $endDate
                ]);
            } else {
                foreach ($daySchedule['schedule'] as $schedule) {
                    $builder->insert([
                        'hour' => $schedule['hour'],
                        'class_id' => $schedule['class_id'],
                        'day' => $daySchedule['day'],
                        'date_day' => $daySchedule['date_day'],
                        'date_week_start' => $startDate,
                        'date_week_end' => $endDate
                    ]);
                }
            }
        }
    }

    function validation($daysNumber, $dateWeekEnd)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('most_recent_schedule');
        $latestSchedule = $builder->get()->getFirstRow();

        if ($daysNumber !== 7) {
            return [
                'error' => true,
                'errorType' => 'incomplete',
                'errorMessage' => 'A week schedule must contain the schedule for only 7 days'
            ];
        }

        if ($dateWeekEnd < $latestSchedule->date_week_end) {
            return [
                'error' => true,
                'errorType' => 'partial overlap',
                'errorMessage' => 'Expected the new week schedule to start after or on '
                    . date('d F', strtotime($latestSchedule->date_week_start))
            ];
        }

        if ($dateWeekEnd == $latestSchedule->date_week_end) {
            return [
                'error' => true,
                'errorType' => 'overlap',
                'errorMessage' => 'Duplicate schedules are not allowed !'
            ];
        }

        return [
            'error' => false,
            'errorType' => null,
            'errorMessage' => null
        ];
    }
}
