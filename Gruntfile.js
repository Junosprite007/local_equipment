"use strict";

module.exports = function (grunt) {
    // Load all grunt tasks
    grunt.loadNpmTasks("grunt-contrib-sass");
    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-exec");

    // Initialize your custom configuration
    grunt.initConfig({
        sass: {
            development: {
                options: {
                    style: "expanded",
                },
                files: {
                    "styles.css": "scss/styles.scss",
                },
            },
            prod: {
                options: {
                    style: "compressed",
                },
                files: {
                    "styles-prod.css": "scss/styles.scss",
                },
            },
        },
        watch: {
            scss: {
                files: ["scss/*.scss"],
                tasks: ["sass:development"],
            },
            amd: {
                files: ["amd/src/**/*.js"],
                tasks: ["exec:amd"],
            },
        },
        exec: {
            amd: {
                cmd: 'cd ../../ && grunt amd --root="local/equipment"',
                stdout: true,
                stderr: true,
            },
        },
    });

    // Register custom tasks
    grunt.registerTask("css", ["sass:development"]);
    grunt.registerTask("css-prod", ["sass:prod"]);
    grunt.registerTask("default", ["watch"]);
};
