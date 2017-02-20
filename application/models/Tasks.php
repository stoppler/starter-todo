<?php

class Tasks extends MY_Model {
        public function __construct()
        {
            parent::__construct('tasks', 'id');
        }
        
        function getCategorizedTasks()
        {
        
            foreach ($this->all() as $task)
            {
                if ($task->status != 2)
                    $undone[] = $task;
            }
         
            foreach ($undone as $task)
                $task->group = $this->groups->get($task->group)->name;
        
            usort($undone, "orderByCategory");

            foreach ($undone as $task)
                $converted[] = (array) $task;
            return $converted;
        }
}