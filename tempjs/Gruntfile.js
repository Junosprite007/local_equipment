"use strict";

module.exports = function (grunt) {
    // Load the core Moodle Gruntfile.
    require("grunt-load-gruntfile")(grunt);
    grunt.loadGruntfile("../../Gruntfile.js");

    // Load necessary grunt tasks.
    grunt.loadNpmTasks("grunt-contrib-sass");
    grunt.loadNpmTasks("grunt-contrib-watch");

    // Initialize your custom configuration.
    grunt.initConfig({
        watch: {
            // Watch SCSS files in your plugin's directory.
            scss: {
                files: "scss/*.scss",
                tasks: ["sass:development"]
            },
            // Watch AMD files in your plugin's directory.
            amd: {
                files: "amd/src/**/*.js",
                tasks: ["amd"]
            }
        },
        build: {
            scss: {
                files: "scss/*.scss",
                tasks: ["sass:development"]
            },
            // Watch AMD files in your plugin's directory.
            amd: {
                files: "amd/src/**/*.js",
                tasks: ["amd"]
            }
        },
        sass: {
            development: {
                options: {
                    style: "expanded"
                },
                files: {
                    "styles.css": "scss/styles.scss"
                }
            },
            prod: {
                options: {
                    style: "compressed" // Minify the CSS.
                },
                files: {
                    "styles-prod.css": "scss/styles.scss"
                }
            }
        }
    });

    // Register your custom tasks.
    grunt.registerTask("default", ["sass:development"]);
    grunt.registerTask("prod", ["sass:prod"]);

    // Register a task to watch both SCSS and AMD files.
    grunt.registerTask("watch-all", ["watch:scss", "watch:amd"]);
};
