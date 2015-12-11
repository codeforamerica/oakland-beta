module.exports = function(grunt) {
    grunt.initConfig({
        sass: {
          dist: {
            files: {
              'public/resources/css/primer.css': 'dev/sass/primer.scss'
            }
          }
        },
        watch: {
          scripts: {
            files: ['dev/sass/*'],
            tasks: 'sass',
            options: {
              spawn: false,
            }
          }
        }
    });

    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-watch')

    grunt.registerTask('default', ['sass']);
}