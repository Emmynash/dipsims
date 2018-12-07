<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $panelInit->settingsArray['siteTitle'] . " | " . $panelInit->language['restorePwd']; ?></title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link href="{{URL::asset('assets/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{URL::asset('assets/bootstrap/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{URL::asset('assets/dist/css/AdminLTE.min.css')}}" rel="stylesheet" type="text/css" />
    <?php if($panelInit->language['position'] == "rtl"){ ?>
        <link href="{{URL::asset('/')}}/assets/css/rtl.css" rel="stylesheet" type="text/css" />
    <?php } ?>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="hold-transition login-page">
    <div class="login-box">
      <div class="login-logo">
          <?php
          if($panelInit->settingsArray['siteLogo'] == "siteName"){
              echo $panelInit->settingsArray['siteTitle'];
          }elseif($panelInit->settingsArray['siteLogo'] == "text"){
              echo $panelInit->settingsArray['siteLogoAdditional'];
          }elseif($panelInit->settingsArray['siteLogo'] == "image"){
              echo "<img src='".URL::asset('assets/img/logo.png')."'/>";
          }
          ?>
      </div><!-- /.login-logo -->
      <div class="login-box-body">
        <p class="login-box-msg"><?php echo $panelInit->language['restorePwd']; ?></p>
        <form action="{{URL::to('/forgetpwd')}}" method="post">
        @if(isset($success))
            <span style='color:green;'>{{$success}}</span><br/><br/>
            <a href="{{URL::to('/')}}" class="text-center"><?php echo $panelInit->language['loginToAccount']; ?></a>
        @else
            @if($errors->any())
                <span style='color:red;'>{{$errors->first()}}</span><br/><br/>
            @endif
            <div class="form-group">
                <input type="text" name="email" class="form-control" placeholder="<?php echo $panelInit->language['userNameOrEmail']; ?>"/>
            </div>

            <button type="submit" class="btn bg-olive btn-block"><?php echo $panelInit->language['restorePwd']; ?></button><br/>
            <a href="{{URL::to('/register')}}" class="text-center"><?php echo $panelInit->language['registerNewAccount']; ?></a>
        @endif

      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->

    <script src="{{URL::asset('assets/plugins/jQuery/jQuery-2.1.4.min.js')}}"></script>
    <script src="{{URL::asset('assets/bootstrap/js/bootstrap.min.js')}}"></script>
  </body>
</html>
