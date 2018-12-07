module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        //start files concatenatoin
        concat: {
            options: {
                separator: ';'
            },
            js: {
                src: ['assets/plugins/jQuery/jQuery-latest.min.js', 'assets/bootstrap/js/bootstrap.min.js', 'assets/dist/js/moment.min.js', 'assets/plugins/humanize-duration/humanize-duration.js', 'assets/plugins/fullcalendar/fullcalendar.min.js', 'assets/plugins/fullcalendar/fullcalendar-lang-all.js', 'assets/js/jquery.gritter.min.js', 'https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js', 'assets/plugins/morris/morris.min.js', 'assets/plugins/knob/jquery.knob.js', 'assets/plugins/datepicker/bootstrap-datepicker.js', 'assets/plugins/chartjs/Chart.min.js', 'assets/plugins/ckeditor/ckeditor.js', 'assets/js/jquery.colorbox-min.js', 'assets/js/intlTelInput.min.js', 'assets/dist/js/app.js', 'assets/js/dipsims.js', 'assets/dist/js/demo.js', 'https://cdnjs.cloudflare.com/ajax/libs/angular.js/1.6.5/angular.min.js', 'assets/js/Angular/AngularModules.js', 'assets/js/Angular/app.js', 'assets/js/Angular/routes.js'],
                dest: 'assets/js/bundle.js'
            },
            css: {
                src: ['assets/bootstrap/css/bootstrap.min.css',/* 'assets/bootstrap/css/font-awesome.min.css','assets/ionicons/css/ionicons.min.css', */ 'assets/dist/css/AdminLTE.min.css', 'assets/css/jquery.gritter.css', 'assets/plugins/fullcalendar/fullcalendar.min.css', 'assets/dist/css/skins/_all-skins.min.css', 'assets/css/intlTelInput.css', 'assets/css/dipsims.css'],
                dest: 'assets/css/bundle.css'
            },

        },
    });

    // Load the plugin that provides the "concat" task.
    grunt.loadNpmTasks('grunt-contrib-concat');

    // Concat task(s).
    grunt.registerTask('build', ['concat']);

};
