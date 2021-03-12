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

    
    /*******************************************************************************************************
     ** Function create a contact with params infos of form 

     *** Method POST, avec la method $this->request->getVar()

     **** Values : 
          - lastName:string, 
          - fistName:string, 
          - company:string, 
          - job:string, 
          - email:email, 
          - phone:integer, 
          - note:string, 
          - favory:string, 
          - image:string/file,
          - createDate:datetime

     ****** Retourne true pour le succes de la creation du contact, ou return false lors d'une erreur lors de la creation du contact

     ******* En cas de true j'affiche un message de succes et en cas de false je renvois un message d'erreur

     ******** Les champs obligatoires : un telephone, un prenom
    ******************************************************************************************************/
	public function create ()
	{
        $etatActions = ['response' => false];

        // On récupère le nom et le téléphone du nouveau contact
        $nameContact = $this->request->getVar('firstName');
        $phoneContact = $this->request->getVar('phone');
        
        // Si les valeurs du formulaire existent et ne sont pas vides
       $rules = [
           'firstName' => 'required',
           'phone'     => 'required'
       ];

    // Si les champs sont remplis 
       if($this->validate($rules))
        {
            // on remplit le tableau de reponse avec true et l'envoi des données
            $etatActions['response'] = true;
            $etatActions["data"] = [
                'firstName' => $nameContact,
                'phone' => $phoneContact,
                
            ];
            
            // On enregistre les données saisies dans un tableau afin de les inserer en base de donnéees
            $addContact = [
                'first_Name'=> $nameContact,
                'phone' => $phoneContact
            ];

            // On insère  les nouvelles données en base de données
            $insert = $this->contactsModel->save($addContact);
          //  $etatActions['data']['id'] = $insert->insertID();

        } else {
            if(empty($nameContact))
            {
                $etatActions['response'] = false;
                $etatActions['error']['fistName'] = "champs requis" ;  
            }
            if(empty($phoneContact))
            {
                $etatActions['response'] = false;
                $etatActions['error']['phone'] = "champs requis" ;  
            }
        }   

        return $this->response->setJSON($etatActions);
    }

    /*******************************************
     * Function edit with params id of contact
     * On vérifie l'existence d'un id 
     * 
    *******************************************/
	public function edit ()
	{
        $etatActions = ['response' => false];

        // On récupère les données saisies
        $nameContact = $this->request->getVar('firstName');
        $phoneContact = $this->request->getVar('phone');

        // on recupère l'id du contact existant
        $id = $this->request->getVar('idContact');
        
        // Si les valeurs du formulaire existent et ne sont pas vides
       $rules = 
       [
           'firstName' => 'required',
           'phone'     => 'required',
           'idContact' => 'required'
       ];

        // Si les champs sont remplis 
       if($this->validate($rules))
        {
            // On verifie l'existence du contact si l'id existe en post
            $ContactID = $this->contactsModel->where('id', $id)->first();
           

             //Si il existe un contact avec l'id saisie
            if(!empty($ContactID))
            {
                // On enregistre les données saisies dans un tableau pour leur mise a jour en base de donnéees
                $updateContact = [
                    'first_Name'=> $nameContact,
                    'phone' => $phoneContact
                    ];

                    // Si le champs n'est pas null on fait la mise a jour sinon on garde les données que l'on a deja en base de donnée
                   if($this->request->getVar('nom') != '')
                    {
                        $updateContact['last_Name'] = $this->request->getVar('nom');
                    }
        
                // On met à jour les nouvelles données en base de données 
                $this->contactsModel->where('id', $ContactID['id'])
                                    ->set($updateContact)
                                    ->update();
                // on envvoie un message de success true au serveur
                $etatActions['response'] = true;
                // renvoi les données saisie 
                $etatActions["data"] = [
                                    'firstName' => $nameContact,
                                    'phone' => $phoneContact,
                                    'idContact' => $id
                                   // 'all' => $allContact
                                ];  
                       
            } else {
           // Si l'identifiant n'existe pas on envoie un message d'erreur 
                $etatActions['response'] = false;
                $etatActions['error']['id'] = "Cet identifiant n'existe pas" ;  
            }
        } else {
            // Si l'un des champs saisie est vide on retourne un message d'erreur
            if(empty($nameContact))
            {
                $etatActions['response'] = false;
                $etatActions['error']['fistName'] = "champs requis" ;  
            }
            if(empty($id))
            {
                $etatActions['response'] = false;
                $etatActions['error']['id'] = "Veuillez saisir un id" ;  
            }

            if(empty($phoneContact))
            {
                $etatActions['response'] = false;
                $etatActions['error']['phone'] = "champs requis" ;  
            }
        }   

        return $this->response->setJSON($etatActions);
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
