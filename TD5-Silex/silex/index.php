<?php
ini_set('date.timezone', 'Europe/Paris');

$loader = include('vendor/autoload.php');
$loader->add('', 'src');

$app = new Silex\Application;
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views'
));

$app['model'] = new sondage\Model(
    '127.0.0.1',  // Hôte
    'sondage',    // Base de données
    'root',    // Utilisateur
    'root'     // Mot de passe
);

	/* ----- Connexion ----- */

$app->post('/login', function() use ($app) {
    $login = $_POST['login'];
    $password = $_POST['password'];
    if ($login != null && $password != null) {
        if($app['model']->checkConnexion($login,$password)) {
        	$idUser = $app['model']->getIdUser($login);
            $app['session']->set('user', array(
                'idUser' => $idUser['id'],
                'login' => $login
            ));
        }
    }
    return $app->redirect("/");
})->bind('login');

$app->match('/login', function() use ($app) {
    return $app['twig']->render('login.html.twig');
})->bind('login-form');

$app->match('/logout', function() use ($app) {
  	$app['session']->clear();
    return $app['twig']->render('logout.html.twig');
})->bind('logout');

	/* ----- Page d'accueil ----- */

$app->match('/', function() use ($app) {
    return $app['twig']->render('index.html.twig', array(
    	'session' => $app['session']->get('user')
    	));
})->bind('index');

	/* ----- Création d'un sondage ----- */

$app->post('/create', function() use ($app) {
	$success = 'non défini';
	$question = $_POST['question'];
	$answer1 = $_POST['answer1'];
	$answer2 = $_POST['answer2'];
	$answer3 = $_POST['answer3'];
	if($app['model']->createPoll($question,$answer1,$answer2,$answer3,$app['session']->get('user')['login'])) {
	    $success = 'true';
	}
	else {
		$success = 'false';
	}
	return $app['twig']->render('create.html.twig', array(
				'created' => $success
			));
})->bind('create');

$app->match('/create', function() use ($app) {
	$success = 'non défini';
    return $app['twig']->render('create.html.twig', array(
    			'created' => $success,
    			'session' => $app['session']->get('user')
    	));
})->bind('create-form');
	
	/* ----- Ajout d'utilisateur ----- */

$app->post('/register', function() use ($app) {
	$success = 'non défini';
	$login = $_POST['login'];
    $password = $_POST['password'];
    if ($login != null && $password != null) {
	    if($app['model']->checkRegister($login,$password)) {
	        $success = 'true';
	    }
	    else {
	    	$success = 'false';
	    }
	}
	return $app['twig']->render('register.html.twig', array(
				'register' => $success
			));
})->bind('register');

$app->match('/register', function() use ($app) {
	$success = 'non défini';
    return $app['twig']->render('register.html.twig', array(
				'register' => $success
			));
})->bind('register-form');

	/* ----- Sondages ----- */

$app->match('/polls', function() use ($app) {
	 return $app['twig']->render('polls.html.twig', array(
			'polls' => $app['model']->getAllPolls()
		));
})->bind('polls');

$app->match('/poll/{id}', function($id) use ($app) {
	return $app['twig']->render('poll.html.twig', array(
			'poll' => $app['model']->getPoll($id),
			'session' => $app['session']->get('user')
		));
})->bind('poll');

$app->post('/answer/{id}/{idUser}', function($id, $idUser) use ($app) {
	
	$answerId = $_POST['answer'];
	$idPoll = $id;
	$poll = $app['model']->getPoll($idPoll);
	$labelAnswers = $app['model']->getLabelAnswers($idPoll);
	if($app['model']->insertAnswer($idUser,$idPoll,$answerId)) {
	    $success = 'true';
	}
	else {
		$success = 'false';
	}
	return $app['twig']->render('answer.html.twig', array(
			'poll' => $poll,
			'answers' => $app['model']->getAnswers($idPoll),
			'total' => $app['model']->getTotalAnswer($idPoll),
			'labelAnswers' => $labelAnswers,
			'submitted' => $success
		));
})->bind('answer');

// Fait remonter les erreurs
$app->error(function($error) {
    throw $error;
});

$app->run();
?>
