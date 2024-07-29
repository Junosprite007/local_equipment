'use strict';

const path = require('path');
const fs = require('fs');

module.exports = function (grunt) {
    // We need to include the core Moodle grunt file too, otherwise we can't run tasks like "amd".
    require('grunt-load-gruntfile')(grunt);
    grunt.loadGruntfile('../../Gruntfile.js');

    // Load all grunt tasks.
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-clean');

    grunt.initConfig({
        watch: {
            // If any .scss file changes in directory "scss" then run the "sass" task.
            styles: {
                files: ['scss/*.scss'],
                tasks: ['sass', 'stylelint'],
            },
            amd: {
                files: ['amd/src/**/*.js'],
                tasks: ['amd'],
            },
        },
        sass: {
            // Production config is also available.
            development: {
                options: {
                    style: 'expanded',
                    loadPath: ['myOtherImports/'],
                },
                files: {
                    'styles.css': 'scss/styles.scss',
                },
            },
            prod: {
                options: {
                    style: 'compressed',
                    loadPath: ['myOtherImports/'],
                },
                files: {
                    'styles-prod.css': 'scss/styles.scss',
                },
            },
        },
        stylelint: {
            options: {
                configFile: '.stylelintrc',
                failOnError: false,
                quiet: false,
            },
            src: ['**/*.css', '**/*.scss'],
        },
    });

    // The default task (running "grunt" in console).
    grunt.registerTask('default', ['sass:development', 'amd']);
    // The production task (running "grunt prod" in console).
    grunt.registerTask('prod', ['sass:prod', 'amd']);
    // Register the stylelint task
    grunt.registerTask('css', ['stylelint', 'sass:development']);
};
