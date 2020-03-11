<?php
class AtendenteService {
  public function getData(){

  try{

  TTransaction::open('permission');



  // $users = SystemUser::getObjects();
  $repository = new TRepository('SystemUser');
  // load the objects according to criteria
  // $repository = new TRepository('Contratos');
  // $limit = 10;
  // creates a criteria
  // $criteria = new TCriteria;
  $users = $repository->load($criteria, FALSE);
  $obj = [];

  foreach($users as $user){

  $obj_user =  ["id"=>$user->id,"name"=>$user->name, "email"=>$user->email];

  $obj[] = $obj_user;
  }


  TTransaction::close();

 return  json_encode($obj);

  }catch(Exception $e){

  echo "error ".$e->getMessage();
  }
}

  public function show(){

  echo $this->getData();

  }
}
