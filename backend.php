  <?php

  if(!isset($_GET['file'])) 
  {
    die('MUST SPECIFY FILE');
  }

  $filename  = dirname(__FILE__) . '/data/' . $_GET['file'] . '.txt';

  if(!file_exists($filename)) {
    touch($filename);
  }

  // store new message in the file
  if (isset($_POST['msg'])) 
  {
    if(!isset($_POST['user'])) 
    {
       die('Must specify user');
    }

    file_put_contents(
      $filename, 
      json_encode(array(
        'id' => uniqid(),
        'user' => $_POST['user'],
        'msg' => $_POST['msg'])) . ",\n",  
      FILE_APPEND);
    die();
  }

  // infinite loop until the data file is not modified
  $lastmodif    = isset($_GET['timestamp']) ? $_GET['timestamp'] : 0;
  $currentmodif = filemtime($filename);
/*  while ($currentmodif <= $lastmodif) // check if the data file has been modified
  {
    usleep(10000); // sleep 10ms to unload the CPU
    clearstatcache();
    $currentmodif = filemtime($filename);
  }*/

  // return a json array
  echo json_encode(array(
    'messages' => json_decode('[' . substr(file_get_contents($filename), 0, -2) .']'), 
    'timestamp' => $currentmodif));

  die();
  //flush();
 
  ?>