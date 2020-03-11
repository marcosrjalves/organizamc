<?php
class AtendenteController extends TPage
 {

    public function onReload(){

            try{

        parent::__construct();
        $html = new THtmlRenderer('app/view/listar.html');

        $html->enableSection('main');

        parent::add($html);

        }
        catch(Exception $e){

        new TMessage('error',$e->getMessage());

        }

    }

    public function show()
    {

      $this->onReload();
      parent::show();
    }


}
