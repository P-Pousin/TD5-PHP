<?php include('header.php'); ?>

<h2>Identification</h2>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!empty(htmlspecialchars($_POST['login'])) && !empty(htmlspecialchars($_POST['password']))) {
            $req = $pdo->query('SELECT * FROM users WHERE login="'.$_POST['login'].'"');
            if ($req->rowCount()) {
                $user = $req->fetch();
                if (md5($user['password']) == htmlspecialchars($_POST['password'])) {
                    $currentUser = $user;
                    $_SESSION['user'] = $currentUser['id'];
?>
<script>document.location='login.php';</script>
<?php
                } else {
?>
<div class="alert alert-danger">
    Mauvais mot de passe.
</div>
<?php
                }
            } else {
?>
<div class="alert alert-danger">
    L'utilisateur <?php echo htmlspecialchars($_POST['login']); ?> n'existe pas.
</div>
<?php
            }
        }
}
?>

<?php if ($currentUser) { ?>
<div class="alert alert-success">
    Vous êtes identifiés en tant que <?php echo htmlspecialchars($currentUser['login']); ?>
</div>
<?php } else { ?>
<form method="post" class="form-horizontal">
    <div class="form-group">
        <label class="col-sm-2" for="login">Identifiant</label>
        <div class="col-sm-10">
            <input required="required" class="form-control" type="text" id="login" name="login" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2" for="password">Mot de passe</label>
        <div class="col-sm-10">
            <input required="required" class="form-control" type="password" id="password" name="password" />
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-2">&nbsp;</div>
        <div class="col-sm-10">
            <input type="submit" class="btn btn-success" value="Enregistrer" />
        </div>
    </div>
</form>
<?php } ?>

<?php include('footer.php'); ?>
