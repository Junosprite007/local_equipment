"use strict";

module.exports = function (grunt) {
    // Load necessary Grunt plugins
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-watch');

    // Load Moodle's Grunt configuration
    var moodleGrunt = require('../../Gruntfile.js');

    // Initialize Grunt configuration
    grunt.initConfig({
        // Custom task for compiling SCSS to CSS
        sass: {
            dist: {
                files: [{
                    expand: true,
                    cwd: 'scss',
                    src: ['*.scss'],
                    dest: './',
                    ext: '.css'
                }]
            }
        },
        // Watch task for SCSS and JavaScript files
        watch: {
            styles: {
                files: ['scss/*.scss'],
                tasks: ['sass'],
                options: {
                    spawn: false,
                },
            },
            scripts: {
                files: ['amd/src/*.js'],
                tasks: ['moodle:amd'],
                options: {
                    spawn: false,
                },
            },
        },
    });

    // Merge Moodle's Grunt configuration
    grunt.config.merge(moodleGrunt);

    // Register custom tasks
    grunt.registerTask('default', ['sass', 'watch']);
    grunt.registerTask('moodle:amd', moodleGrunt.amd);

    // Register a task to run Moodle's watch task
    grunt.registerTask('moodle:watch', moodleGrunt.watch);
};
