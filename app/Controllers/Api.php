<?php

namespace App\Controllers;
//use App\Controllers\BaseController;

use App\Models\ContactsModel;

class Api extends BaseController
{
    public $contactsModel = null;
	
	public function __construct()
	{
		/******** Initialisation des models appelés  *****/
		 $this->contactsModel = new ContactsModel();
	}

    /*************************************
     * Function list and function search
    **************************************/
	public function index()
	{
        $termeRecherche = $this->request->getVar('TypedeRecherche');
        $elementRecherche = $this->request->getVar('element');
        $listContacts = $this->contactsModel->orderBy('last_Name', 'ASC')->orderBy('first_Name', 'ASC')->paginate(12);

       if(!empty($termeRecherche) && !empty($elementRecherche))
		{
			switch($termeRecherche)
			{
				case "recherche":
                    $listContacts = $this->contactsModel->like('first_Name', $elementRecherche, "both", null, true)
                                                        ->Orlike('last_Name', $elementRecherche, "both", null, true)
                                                        ->orderBy('last_Name', 'ASC')
                                                        ->orderBy('first_Name', 'ASC')
                                                        ->paginate(2);
                    break;
			}

		}
        return $this->response->setJSON($listContacts);

        //$listContacts->setHeader('Content-Type: application/json');

        //echo json_encode($listContacts);
	}

    /*******************************************
     * Function edit with params id of contact
    *******************************************/
	public function edit ($idContact = null)
	{
        
	}

    /*********************************************
     * Function delete with params id of contact
    ********************************************/
	public function delete ($idContact = null)
	{
        
	}

    /*********************************************
     * Function favoris with params id of contact
    ********************************************/
	public function favoris ($idContact = null)
	{
        
	}
}
