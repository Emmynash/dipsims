<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Installation</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
       	<link href="<?php echo URL::asset('/'); ?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo URL::asset('/'); ?>assets/bootstrap/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo URL::asset('/'); ?>assets/css/datepicker3.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo URL::asset('/'); ?>assets/css/jquery-ui.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo URL::asset('/'); ?>assets/css/jquery.gritter.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo URL::asset('/'); ?>assets/css/fullcalendar.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo URL::asset('/'); ?>assets/dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo URL::asset('/'); ?>assets/css/Kamrul&Tanzila.css" rel="stylesheet" type="text/css" />
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="hold-transition login-page rtlPage" ng-app="Kamrul&Tanzila" ng-controller="registeration">
      <div class="login-box">
        <div class="login-box-body">
          <p class="login-box-msg">Upgrade to Kamrul&Tanzila 2.2</p>
          <form action="{{URL::to('/upgrade')}}" method="post">
              <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
              <div class="body">
                  @if($errors->any())
                     <h4 style='color:red;'>{{$errors->first()}}</h4>
                  @endif

                  @if($currStep == "welcome")
                      <center>Thank you for choosing Kamrul&Tanzila for your school administration.
                      <br/>
                      <?php
                        if(isset($loginError)){
                            ?><h4 style='color:red;'>Credentials error or invalid purchase code</h4><?php
                        }
                      ?>
                      Please login with administrator account to start Kamrul&Tanzila upgrade</center>
                      <div class="form-group">
                          <input type="text" name="email" class="form-control" placeholder="Username or e-mail address"/>
                      </div>
                      <div class="form-group">
                          <input type="password" name="password" class="form-control" placeholder="Password"/>
                      </div>
                      <div class="form-group">
                          <input type="text" name="cpc" class="form-control" placeholder="Codecanyon Purchase Code"/>
                      </div>
                      <input type="hidden" name="nextStep" value="1">
                      <button type="submit" class="btn bg-olive btn-block">Start upgrade process</button>
                  @endif
                  @if($currStep == "1")
                      <div class="form-group">
                          <center><b>Permissions Tests</b> <br/> The following folders needs write permissions</center>
                      </div>

                      <div class="form-group" style="color:green;">
                          <?php
                              if(isset($success)){
                                  while (list($key, $value) = each($success)) {
                                      echo $value.": <b>Success</b> <br/>";
                                  }
                              }
                          ?>
                      </div>
                      <div class="form-group" style="color:red;">
                          <?php
                              if(isset($perrors)){
                                  while (list($key, $value) = each($perrors)) {
                                      echo $value.": <b>Failed</b> <br/>";
                                  }
                              }
                          ?>
                      </div>
                      @if($nextStep == "1")
                          <input type="hidden" name="nextStep" value="1">
                          <button type="submit" class="btn bg-olive btn-block">Recheck</button>
                      @endif
                      @if($nextStep == "2")
                          <input type="hidden" name="nextStep" value="2">
                          <button type="submit" class="btn bg-olive btn-block">Next Step</button>
                      @endif
                  @endif

                  @if($currStep == "2")
                      <div class="form-group">
                          <center><b>Database upgrade</b></center>
                      </div>

                      <div class="form-group">
                          <center><b>Database upgraded successfully</b></center>
                      </div>

                      <input type="hidden" name="nextStep" value="3">
                      <button type="submit" class="btn bg-olive btn-block">Next Step</button>
                  @endif

                  @if($currStep == "3")
                      <div class="form-group">
                          <center><b>Thank You for upgrading Kamrul&Tanzila</b></center>
                      </div>
                      <a href="<?php echo URL::to('/'); ?>" class="btn bg-olive btn-block">Start using Kamrul&Tanzila</a>
                  @endif
              </div>
              <div class="footer">
                  <br/>
                  <a href="http://www.cr-house.com" target="_BLANK" class="btn bg-olive btn-block">Creation House</a>
              </div>
          </form>
        </div><!-- /.login-box-body -->
      </div><!-- /.login-box -->

        <script src="<?php echo URL::asset('/'); ?>assets/plugins/jQuery/jQuery-2.1.4.min.js"></script>
        <script src="<?php echo URL::asset('/'); ?>assets/js/jquery-ui.min.js" type="text/javascript"></script>
        <script src="<?php echo URL::asset('/'); ?>assets/js/jquery.gritter.min.js" type="text/javascript"></script>

        <script src="<?php echo URL::asset('/'); ?>assets/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

        <script src="<?php echo URL::asset('/'); ?>assets/js/Kamrul&Tanzila.js" type="text/javascript"></script>
    </body>
</html>
