<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Tasks
 *
 * @author Kevin
 */
class Tasks extends MY_Model {

        public function __construct()
        {
                parent::__construct('tasks', 'id');
        }

}