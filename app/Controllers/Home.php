<?php

namespace App\Controllers;
use App\Controllers\BaseController;

use App\Models\ContactsModel;

class Home extends BaseController
{
	public function index()
	{

		echo view('common/HeaderMonsite');
		echo view('Site/index');
		echo view('common/FooterMonsite');
	}
}
