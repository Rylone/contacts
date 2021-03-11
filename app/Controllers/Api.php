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
        		/******** Je recupere la valeur envoyé par la client en post  *****/
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
                                                        ->paginate(8);
                    break;
			}

		}
            if(!$listContacts)
            {
                $error = ["response" => "Ce contact n'existe pas"];
                return $this->response->setJSON($error);
            }
        return $this->response->setJSON($listContacts);

        //$listContacts->setHeader('Content-Type: application/json');

        //echo json_encode($listContacts);
	}

    /*******************************************
     * Function edit with params id of contact
    *******************************************/
	public function edit ()
	{
        $idContact = $this->request->getVar('idContact');
        $save = $this->request->getVar('update');
        // si le formulaire est soumit
        if(isset($save))
        {
            $this->validate->setRules([
                'firstName' => [
                    'rules'  => 'required|is_unique[contacts.first_Name]',
                    'errors' => [
                        'required' => 'All accounts must have {field} provided'
                    ]
                ]
                ]);
           
        
         
        }
       
	}

    /*********************************************
     * Function delete with params id of contact
    ********************************************/
	public function delete()
	{
        /****   1) Je recupère l'identifiant du contact a supprimer *****/
        $idContact = $this->request->getVar('idContact');

        /****   2) Si l'idcontact existe je passe a la troisième etape *****/
        if(isset($idContact) && !empty($idContact))
        {
        /*****  3) Je crée ma requete pour la suppression ***********/
            $deleteContact = $this->contactsModel->where('id', $idContact)->delete();

        /****  4) J'indique l'etat de ma suppression *********/
            return $this->response->setJSON(['response'=>true]);
        }  
       return $this->response->setJSON(['response'=>false]);
	}

    /*********************************************
     * Function favoris with params id of contact
    ********************************************/
	public function favorite()
	{
         /****   Je recupère l'identifiant du contact a mettre a jour *****/
         $idContact = $this->request->getVar('idContact');

         /****    Si l'id contact existe je passe a la troisième etape *****/
         if(isset($idContact) && !empty($idContact))
         {
            /****  Je cherche le contact concerné *****/
             $contact = $this->contactsModel->where('id', $idContact)->first();
            /****  Si il existe un contact et qu'il n'est pas en favoris *****/
            if(!empty($contact))
            {  
               /* version symplifié du monsieur
                 Traduction => Si favoris est egale ou No il va etre egale a Yes sinon, il sera egale ou No :
                 $favoris = ($contact['favory'] == "No")?'Yes':'No' ;
                 $favorisContact = $this->contactsModel->where('id', $idContact)->set('favory',  $favoris)->update();
                 return $this->response->setJSON(['response'=>true]); 
                */
                
                /* version detaillée */
                /** Si favory est egale à no */
                if($contact['favory'] == "No")
                {
                     /*****   Je crée ma requete pour l'ajout de favoris ***********/
                    $favorisContact = $this->contactsModel->where('id', $idContact)->set('favory', 'Yes')->update();
                    /****  J'indique l'etat de ma suppression *********/
                    return $this->response->setJSON(['response'=>true]);
                }
                   /*****   Je crée ma requete pour retirer le favoris car favoris est egale à yes ***********/
                   $favorisContact = $this->contactsModel->where('id', $idContact)->set('favory', 'No')->update();
                   /****  J'indique l'etat de ma mise a jour  *********/
                   return $this->response->setJSON(['response'=>true]);
            }
         }  

        return $this->response->setJSON(['response'=>false]);
	}
}
