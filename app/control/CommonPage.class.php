<?php
/**
 * CommonPage
 *
 * @version    1.0
 * @package    control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class CommonPage extends TPage
{
    public function __construct()
    {
        parent::__construct();
        $welcome = new TLabel('Bem vindo ao sistema ORGANIZA MEU CASAMENTO!');
        $welcome->setFontSize(18);
        parent::add($welcome);
    }
}
