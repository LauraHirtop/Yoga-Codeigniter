<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Models\SchedulesWeeksModel;
use App\Models\UsersModel;
use App\Models\BookingsModel;
use CodeIgniter\HTTP\RequestInterface;

class Bookings extends BaseController
{
    use ResponseTrait;

    public function __construct()
    {
        $this->request = \Config\Services::request();
    }

    public function index()
    {
    }

    public function getClassBookings($weekScheduleId){
        $bookingsModel = new BookingsModel();

        return $this->setResponseFormat('json')->respond([
            'status' => 201,
            'error' => null,
            'data' => $bookingsModel->getClassBookings($weekScheduleId)
        ]);
    }

    public function postBooking($weekScheduleId, $classType)
    {
        $usersModel = new UsersModel();
        $jwt = $this->request->getHeader('Authorization')->getValue();
        $user = $usersModel->queryUser('jwt', $jwt);

        if (!$user) {
            return $this->respond([
                'status' => 401,
                'error' => 'Not authorized'
            ]);
        }

        $bookingsModel = new BookingsModel();
        $bookingsModel->insertBooking($user->id, $weekScheduleId, $classType);

        return $this->setResponseFormat('json')->respond([
            'status' => 201,
            'error' => null,
            'data' => [
                'message' => 'class successfully booked !'
            ]
        ]);
    }
}
