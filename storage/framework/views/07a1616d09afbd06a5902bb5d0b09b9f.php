<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config("app.name")); ?></title>

<style type="text/css">

    .myBody{
        box-sizing: border-box;
        font-family: 'Nunito', sans-serif;
        font-family: 'Segoe UI','Roboto',Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
        background-color: #edf2f7;
        margin: 0;
        padding: 0;
        width: 100%;
    }

    .myStyle{
        box-sizing: border-box;
        font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
        padding: 25px 0;
        text-align: center;
        margin: 0;
        /* padding: 23px;background: #edf2f7; box-sizing: border-box; */
    }

    .content{
        box-sizing: border-box;
        font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
        background-color: #ffffff;
        border-color: #e8e5ef;
        border-radius: 2px;
        border-width: 1px;
        margin: 0 auto;
        padding: 0;
        width: 570px;
        color: #222;
    }

    .contenu{
        box-sizing: border-box;
        font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
        padding: 32px;
        font-size: 16px;
        line-height: 1.5em;
        margin-top: 0;
        text-align: left;
        color: #222;
    }

    .myFooter{
        box-sizing: border-box;
        font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';
        margin: 0 auto;
        padding: 0;
        text-align: center;
        width: 570px;
        max-width: 100vw;
        padding: 32px;
        line-height: 1.5em;
        margin-top: 0;
        color: #b0adc5;
        font-size: 12px;
        text-align: center;
    }

</style>

</head>
<body class="myBody">

<center>
    <h2 class="myStyle">
        <a href="javascript:void(0)"><?php echo $details["subject"]; ?></a>
    </h2>
</center>

<div class="content">

    <div class="contenu" style="color: #718096;">

        <p>
            <?php echo $details['content']["greeting"]; ?>

        </p>

        <p>
            <?php echo $details['content']["introduction"]; ?>

        </p>
        <p>
            Identifiant : <?php echo $details['content']["identifiant"]; ?>

        </p>
        <p>
            Mot de passe : <?php echo $details['content']["password"]; ?>

        </p>
        <p>
            Voici le lien de connexion
        </p>

        <a href="<?php echo e($details['content']['lien']); ?>"><?php echo e($details['content']['lien']); ?></a>

    </div>
</div>


<center>
    <p class="myStyle" style="color: #b0adc5;">
        &copy; 2024 <?php echo e(config("app.name")); ?>. All rights reserved.
    </p>
</center>

</body>
</html>
<?php /**PATH /home/unknow/GDIZ/apps/backend_api/resources/views/emails/auth/confirmation_compte.blade.php ENDPATH**/ ?>