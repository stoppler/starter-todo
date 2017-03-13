<?php
class Mtce extends Application {
		private $items_per_page = 10;
		public function index()
		{
		     $this->page(1);
		}
		// Show a single page of todo items
		private function show_page($tasks)
		{
                    $role = $this->session->userdata('userrole');
                    $this->data['pagetitle'] = 'TODO List Maintenance ('. $role . ')';
		    // build the task presentation output
		    $result = ''; // start with an empty array
		    foreach ($tasks as $task)
		    {
		        if (!empty($task->status))
		            $task->status = $this->statuses->get($task->status)->name;
                        if ($role == ROLE_OWNER)
                                $result .= $this->parser->parse('oneitemx', (array) $task, true);
                        else
                                $result .= $this->parser->parse('oneitem', (array) $task, true);
		    }
		    $this->data['display_tasks'] = $result;
		    // and then pass them on
		    $this->data['pagebody'] = 'itemlist';
		    $this->render();
		}
		// Extract & handle a page of items, defaulting to the beginning
		function page($num = 1)
		{
		    $records = $this->tasks->all(); // get all the tasks
		    $tasks = array(); // start with an empty extract
		    // use a foreach loop, because the record indices may not be sequential
		    $index = 0; // where are we in the tasks list
		    $count = 0; // how many items have we added to the extract
		    $start = ($num - 1) * $this->items_per_page;
		    foreach($records as $task) {
		        if ($index++ >= $start) {
		            $tasks[] = $task;
		            $count++;
		        }
		        if ($count >= $this->items_per_page) break;
		    }
                    $this->data['pagination'] = $this->pagenav($num);
                    $role = $this->session->userdata('userrole');
                    if ($role == ROLE_OWNER)
                            $this->data['pagination'] .= $this->parser->parse('itemadd',[], true);
                    $this->show_page($tasks);
		}
		// Build the pagination navbar
		private function pagenav($num) {
		    $lastpage = ceil($this->tasks->size() / $this->items_per_page);
		    $parms = array(
		        'first' => 1,
		        'previous' => (max($num-1,1)),
		        'next' => min($num+1,$lastpage),
		        'last' => $lastpage
		    );
		    return $this->parser->parse('itemnav',$parms,true);
		}
                // Initiate adding a new task
                public function add()
                {
                    $task = $this->tasks->create();
                    $this->session->set_userdata('task', $task);
                    $this->showit();
                }
                // initiate editing of a task
                public function edit($id = null)
                {
                    if ($id == null)
                        redirect('/mtce');
                    $task = $this->tasks->get($id);
                    $this->session->set_userdata('task', $task);
                    $this->showit();
                }
                // Render the current DTO
                private function showit()
                {
                    $task = $this->session->userdata('task');
                    $this->data['id'] = $task->id;
                    foreach ($this->priorities->all() as $record)
                    {
                        $priparms[$record->id] = $record->name;
                    }
					foreach ($this->sizes->all() as $record)
                    {
                        $priparms2[$record->id] = $record->name;
                    }
					foreach ($this->groups->all() as $record)
                    {
                        $priparms3[$record->id] = $record->name;
                    }
					foreach ($this->statuses->all() as $record)
                    {
                        $priparms4[$record->id] = $record->name;
                    }
                    $fields = array(
                        'ftask' => makeTextField('Task description', 'task', $task->task, 'Work', "What needs to be done?"),
                        'fpriority' => makeComboBox('Priority', 'priority', $task->priority, $priparms, "How important is this task?"),
						'fsize' => makeComboBox('Size', 'size', $task->size, $priparms2, "How big is this task?"),
                        'fgroup' => makeComboBox('Category', 'group', $task->group, $priparms3, "What category is this task in?"),
						'fstatus' => makeComboBox('Status', 'status', $task->status, $priparms4, "Is the task done or not done?"),
                        'zsubmit' => makeSubmitButton('Update the TODO task', "Click on home or <back> if you don't want to change anything!", 'btn-success'),
                    );
                    $this->data = array_merge($this->data, $fields);
                    $this->data['pagebody'] = 'itemedit';
                    $this->render();
                }
                // handle form submission
                public function submit()
                {
                    // setup for validation
                    $this->load->library('form_validation');
                    $this->form_validation->set_rules($this->tasks->rules());
                    // retrieve & update data transfer buffer
                    $task = (array) $this->session->userdata('task');
                    $task = array_merge($task, $this->input->post());
                    $task = (object) $task;  // convert back to object
                    $this->session->set_userdata('task', (object) $task);
                    // validate away
                    if ($this->form_validation->run())
                    {
                        if (empty($task->id))
                        {
                            $this->tasks->add($task);
                            $this->alert('Task ' . $task->id . ' added', 'success');
                        } else
                        {
                            $this->tasks->update($task);
                            $this->alert('Task ' . $task->id . ' updated', 'success');
                        }
                    } else
                    {
                        $this->alert('<strong>Validation errors!<strong><br>' . validation_errors(), 'danger');
                    }
                    $this->showit();
                }
                // Forget about this edit
                function cancel() {
                    $this->session->unset_userdata('task');
                    redirect('/mtce');
                }
                // Delete this item altogether
                function delete()
                {
                    $dto = $this->session->userdata('task');
                    $task = $this->tasks->get($dto->id);
                    $this->tasks->delete($task->id);
                    $this->session->unset_userdata('task');
                    redirect('/mtce');
                }
}