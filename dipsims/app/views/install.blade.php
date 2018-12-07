<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>dipSIMS Installation</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
       	<link href="<?php echo URL::asset('/'); ?>assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo URL::asset('/'); ?>assets/bootstrap/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo URL::asset('/'); ?>assets/css/datepicker3.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo URL::asset('/'); ?>assets/css/jquery-ui.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo URL::asset('/'); ?>assets/css/jquery.gritter.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo URL::asset('/'); ?>assets/css/fullcalendar.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo URL::asset('/'); ?>assets/dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo URL::asset('/'); ?>assets/css/dipsims.css" rel="stylesheet" type="text/css" />
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="hold-transition login-page rtlPage" ng-app="dipsims" ng-controller="registeration">
      <div class="login-box">
        <div class="login-box-body">
          <p class="login-box-msg">Installation</p>
          <form action="{{URL::to('/install')}}" method="post">
              <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
              <div class="body">
                  @if($errors->any())
                     <h4 style='color:red;'>{{$errors->first()}}</h4>
                  @endif

                  @if($currStep == "welcome")
                      <center>Thank you for choosing dipSIMS for your school administration.
                      <br/>
                      Please follow the next steps to complete dipSIMS installation</center>
                      <br/>
                      <input type="hidden" name="nextStep" value="1">
                      <button type="submit" class="btn btn-primary btn-block btn-flat">Start installation</button>
                  @endif
                  @if($currStep == "1")
                      <div class="form-group">
                          <center><b>Database Connection</b></center>
                      </div>

                      <?php
                          if(isset($dbError)){
                            var_dump($dbError);
                              echo '<div class="form-group" style="color:red;">Database connection failed</div>';
                          }else{
                              echo '<div class="form-group" style="color:green;">Database connection success</div>';
                          }
                      ?>

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
                          <center><b>Administrator details</b></center>
                      </div>

                      <?php
                      if(isset($installErrors)){
                          echo '<div class="form-group" style="color:red;">';
                          while (list($key, $value) = each($installErrors)) {
                              echo $value."<br/>";
                          }
                          echo '</div>';
                      }
                      ?>
                      <div class="form-group">
                          <input type="text" name="fullName" class="form-control" placeholder="Fullname *"/>
                      </div>
                      <div class="form-group">
                          <input type="text" name="username" class="form-control" placeholder="Username *"/>
                      </div>
                      <div class="form-group">
                          <input type="text" name="email" class="form-control" placeholder="E-mail address *"/>
                      </div>
                      <div class="form-group">
                          <input type="password" name="password" class="form-control" placeholder="Password *"/>
                      </div>
                      <div class="form-group">
                          <input type="password" name="repassword" class="form-control" placeholder="Re Password *"/>
                      </div>

                      <div class="form-group">
                          <center><b>Site settings</b></center>
                      </div>

                      <div class="form-group">
                          <input type="text" name="siteTitle" class="form-control" placeholder="Site Title *"/>
                      </div>
                      <div class="form-group">
                          <input type="text" name="systemEmail" class="form-control" placeholder="System e-mail address *"/>
                      </div>

                      <div class="form-group">
                          <center><b>Default academic year</b></center>
                      </div>

                      <div class="form-group">
                          <input type="text" name="yearTitle" class="form-control" placeholder="Deault academic year title *"/>
                      </div>

                      <div class="form-group">
                          <center><b>License</b></center>
                      </div>

                      <div class="form-group">
                          <input type="text" name="cpc" class="form-control" placeholder="dipSIMS Purchase Code *"/>
                      </div>

                      <input type="hidden" name="nextStep" value="3">
                      <button type="submit" class="btn bg-olive btn-block">Next Step</button>
                  @endif

                  @if($currStep == "3")
                      <div class="form-group">
                          <center><b>Thank You for installing dipSIMS</b></center>
                      </div>
                      <a href="<?php echo URL::to('/'); ?>" class="btn bg-olive btn-block">Start using dipSIMS</a>
                  @endif
              </div>
              <div class="form-group has-feedback">
                  <br/><a href="http://www.digitalpatterns.xyz" target="_BLANK" class="btn bg-olive btn-block">DigitalPatterns Ltd</a>
              </div>
          </form>
        </div><!-- /.login-box-body -->
      </div><!-- /.login-box -->

        <script src="<?php echo URL::asset('/'); ?>assets/plugins/jQuery/jQuery-2.1.4.min.js"></script>
        <script src="<?php echo URL::asset('/'); ?>assets/js/jquery-ui.min.js" type="text/javascript"></script>
        <script src="<?php echo URL::asset('/'); ?>assets/js/jquery.gritter.min.js" type="text/javascript"></script>

        <script src="<?php echo URL::asset('/'); ?>assets/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>

        <script src="<?php echo URL::asset('/'); ?>assets/js/dipsims.js" type="text/javascript"></script>
    </body>
</html>
