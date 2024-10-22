// This file is part of FLIP Plugins for Moodle
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * JavaScript for bulk family upload functionality.
 *
 * @module     local_equipment/bulkfamilyupload
 * @copyright  2024 Joshua Kirby <josh@funlearningcompany.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import { get_string as getString } from 'core/str';
import Log from 'core/log';
// import { get } from 'grunt';

/**
 * Initialize the module.
 */
export const init = () => {
    const $textarea = $('#id_familiesinputdata');
    const $preprocessDiv = $('#family-preprocess-display');
    const $preprocessButton = $('.preprocessbutton');
    const $submitButton = $('#id_submitbutton');
    const $partnershipData = $('#id_partnershipdata');
    const partnershipDataValue = JSON.parse(
        $partnershipData.attr('data-partnerships')
    );
    const $courseData = $('#id_coursedata');
    const courseDataValue = JSON.parse($courseData.attr('data-courses'));

    // Set initial height of preprocess div to match textarea
    $preprocessDiv.css('height', $textarea.outerHeight() + 'px');

    // Disable submit button when textarea content changes
    $textarea.on('input', () => {
        $submitButton.prop('disabled', true);
    });

    // Function to clean up input text with error handling
    const cleanInputText = (text) => {
        Log.debug('text: ');
        Log.debug(text);
        if (!text || typeof text !== 'string') {
            return '';
        }
        // Trim newlines and whitespace from the start and end of the entire input
        return text.replace(/^\s+|\s+$/g, '');
    };

    $preprocessButton.on('click', async (e) => {
        e.preventDefault(); // Prevent form submission

        // Clean up input text

        try {
            $textarea.val(cleanInputText($textarea.val()));
            const data = {
                input: $textarea.val(),
                partnerships: partnershipDataValue,
                courses: courseDataValue,
            };

            const families = await validateFamilyData(data);

            Log.debug('families: ');
            Log.debug(families);
            $preprocessDiv.html(families.html);

            // Adjust height of preprocess div to match textarea
            $preprocessDiv.css('height', $textarea.outerHeight() + 'px');

            // Enable the submit button if there are no error messages
            const hasErrors = families.html.includes('alert-danger');
            $submitButton.prop('disabled', hasErrors);

            const messageKey = hasErrors
                ? 'preprocessing_failure'
                : 'preprocessing_success';
            const message = await getString(messageKey, 'local_equipment');
            Log.debug(message);
        } catch (error) {
            Log.error('Error in preprocessing: ');
            Log.error(error);
            $submitButton.prop('disabled', true);
            const errorMessage = await getString(
                'errorvalidatingfamilydata',
                'local_equipment'
            );
            Log.debug(errorMessage);
        }
    });

    // Adjust preprocess div height on window resize
    $(window).on('resize', () => {
        $preprocessDiv.css('height', $textarea.outerHeight() + 'px');
    });
};

/**
 * Validate and process family data.
 *
 * @param {Object} data - The input data object.
 * @param {string} data.input - The raw input string containing family data.
 * @param {Object} data.partnerships - Partnership data keyed by ID.
 * @param {Object} data.courses - Course data keyed by ID.
 * @return {Promise<string>} The HTML feedback string.
 */
export const validateFamilyData = async ({ input, partnerships, courses }) => {
    // let familiesData = [];
    // let familiesHTML = [];
    Log.debug('input: ');
    Log.debug(input);
    // Log.debug(partnerships);
    // Log.debug(courses);
    if (!input || typeof input !== 'string') {
        throw new Error(
            getString(
                'invalidinput',
                'local_equipment',
                getString('expectedanonemptystring', 'local_equipment')
            )
        );
    }
    // This creates and objext with text types as keys and regexes to match as values.
    const regexes = {
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        phone: /^(\+?\d{1,2}\s?)?(\(?\d{3}\)?[\s.-]?)?\d{3}[\s.-]?\d{4}$/,
        name: /^[a-zA-Z\s'-]+$/,
        partnership: /^-?\d+$/,
        student: /^\*(?!\*)(.)/,
        courses: /^\*\*.*$/,
    };

    /**
     * Determine the type of text based on regex patterns.
     * Below, we are defining a function that will take the above oject using and convert it into an array of key/values.
     * So instead of having an object like this:
     * textType1: /regex1/
     * textType2: /regex2/
     * textType3: /regex3/
     * textType4: /regex4/
     * We will have only the one matched key/value pair returned in the form of an array like this:
     * ['textTypeX', /regexX/]
     * ('X' representing whichever key/value pair was matched).
     * It's the Object.entries(regexes) function that does all that. Then, the find() function comes in.
     * The find() function, in this case, destuctures the array that was returned by Object.entries(regexes), skipping the first
     * element (the key) and assigning the second element (the value) to the variable 'regex', which has the test() function.
     *
     * Returns the key of the first element in the array that matches the regex.
     * So, determineTextType() should return one of the following:
     * 'email'
     * 'phone'
     * 'name'
     * 'partnership'
     * 'student'
     * 'courses'
     * 'unknown'
     *
     * @param {string} line - The line of text to analyze.
     * @return {string} The determined text type.
     */
    const determineTextType = (line) => {
        for (const [type, regex] of Object.entries(regexes)) {
            if (regex.test(line)) {
                if (type === 'phone' && line.length < 10) {
                    return 'unknown';
                }
                return type;
            }
        }
        return 'unknown';
    };

    /**
     * Parse and reformat a phone number into +12345678910 format.
     * Take any phone number, and turn it into a U.S. phone number.
     * E.g +1 (234) 567-8910 -> +12345678910
     * E.g. 234-567-8910 -> +12345678910
     * E.g. 234.567.8910 -> +12345678910
     * E.g. 234 567 8910 -> +12345678910
     * E.g. 2345678910 -> +12345678910
     * E.g. 12345678910 -> +12345678910
     * @param {string} phoneNumber - The phone number to reformat.
     * @return {Object} An object containing the reformatted phone number and any errors.
     */
    const parsePhoneNumber = async (phoneNumber) => {
        let parsedPhoneNumber = phoneNumber.replace(/[()\-\s+.]/g, '');
        if (parsedPhoneNumber.length === 10 && parsedPhoneNumber[0] !== '1') {
            parsedPhoneNumber = '+1' + parsedPhoneNumber;
        } else if (
            parsedPhoneNumber.length === 11 &&
            parsedPhoneNumber[0] === '1'
        ) {
            parsedPhoneNumber = '+' + parsedPhoneNumber;
        } else {
            return (
                '<span class="pl-2 pr-2 alert-danger">' +
                (await getString(
                    'invalidphonenumber',
                    'local_equipment',
                    parsedPhoneNumber
                )) +
                '</span>'
            );
        }
        return parsedPhoneNumber;
    };

    /**
     * Process parent information from a line of text.
     *
     * @param {string} line - The line of text to process.
     * @param {string} textType - The type of text determined.
     * @param {Object} parent - The current parent object.
     * @return {Object} Updated parent object.
     */
    const processParentInfo = async (line, textType, parent) => {
        // let parentObj = {
        //     name: {
        //         html: '',
        //         data: '',
        //     },
        //     phone: {
        //         html: '',
        //         data: '',
        //     },
        //     email: {
        //         html: '',
        //         data: '',
        //     },
        // };
        parent = {
            ...parent,
            [textType]: {
                html: line,
                data: line,
            },
        };
        // parentObj[textType] = {
        //     html: line,
        //     data: line,
        // };
        // Log.debug('parent stuff: ');
        // Log.debug(textType);
        // Log.debug(line);
        switch (textType) {
            // case 'name':
            //     break;
            case 'email':
                parent[textType].html =
                    '<span class="pl-4 pr-4">' + line + '</span>';
                // parentObj = {
                //     line: '<span class="pl-4 pr-4">' + line + '</span>',
                //     parent: { ...parent, [textType]: line },
                //     partnership,
                // };
                break;
            case 'phone': {
                let formattedPhone = await parsePhoneNumber(line);
                parent[textType].data = formattedPhone;
                parent[textType].html =
                    '<span class="pl-4 pr-4">' + formattedPhone + '</span>';
                break;

                // return {
                //     line: '<span class="pl-4 pr-4">' + line + '</span>',
                //     parent: { ...parent, [textType]: line },
                //     partnership,
                // };
            }
            default:
                // return { parent, partnership };
                break;
        }
        return parent;
    };

    /**
     * Process student information from a line of text.
     *
     * @param {string} line - The line of text to process.
     * @param {string} textType - The type of text determined.
     * @param {Object} student - The current student object.
     * @return {Object} Updated student object.
     */
    const processStudentInfo = async (line, textType, student) => {
        student = {
            ...student,
            [textType]: {
                html: line,
                data: line,
            },
        };

        switch (textType) {
            case 'student': {
                // This refers to the student's name, which is the only line that is preceded by a single asterisk (*).
                const name = line.replace('*', '').trim();

                student = {
                    ...student,
                    [textType]: {
                        data: name,
                        html: name,
                    },
                };
                // student[textType].data = name;
                // student[textType].html = name;

                // return { ...student, name, line: name };
                break;
            }
            case 'email':
                student[textType].data = line;
                student[textType].html =
                    '<span class="pl-4 pr-4">' + line + '</span>';
                // return {
                //     line: '<span class="pl-4 pr-4">' + line + '</span>',
                //     student: { ...student, [textType]: line },
                // };
                break;
            case 'phone': {
                let formattedPhone = await parsePhoneNumber(line);
                student[textType].data = formattedPhone;
                student[textType].html =
                    '<span class="pl-4 pr-4">' + formattedPhone + '</span>';
                break;
            }
            case 'courses': {
                // student[textType].data =

                const coursesData = line
                    .replace('**', '')
                    .trim()
                    .split(',')
                    .map((course) => course.trim());

                // courseIds.map(async (id) => {
                //     student[textType] = {
                //         ...student[textType],
                //             html: line,
                //             data: line,
                //     };
                // });
                // let coursesHTML = '';

                const coursesHTML = await Promise.all(
                    coursesData.map(async (id) => {
                        let courseName = '';
                        if (courses[id]) {
                            const enDash = '–'; // EN DASH character: '–' or \u2013
                            const regex = new RegExp(`${id} ${enDash} `, 'g');
                            courseName = courses[id].replace(regex, '');
                        } else {
                            const errorMessage = await getString(
                                'idnotfound',
                                'local_equipment',
                                id
                            );
                            courseName = `<span class="pl-2 pr-2 alert-danger">${errorMessage}</span>`;
                        }

                        Log.debug('courseName: ', courseName);
                        Log.debug('id: ', id);

                        return courseName;
                    })
                ).join(', ');

                // Log.debug('studentCourses: ', studentCourses);

                // return {
                //     student: { ...student, courses: studentCourses },
                //     line: `<span class="pl-4 pr-4">${studentCourses.join(
                //         ', '
                //     )}</span>`,
                // };
                student[textType].data = coursesData;
                student[textType].html = coursesHTML;
                break;
            }
            default:
                break;
        }
        return student;
    };

    /**
     * Process partnership information from a line of text.
     *
     * @param {string} id - The partnership ID.
     * @return {Object} The partnership object.
     */

    const processPartnershipInfo = async (id) => {
        let partnership = {};
        if (partnerships[id]) {
            partnership = {
                data: id,
                html: partnerships[id].name,
            };
        } else {
            partnership = {
                data: id,
                html:
                    '<span class="pl-2 pr-2 alert-danger">' +
                    (await getString(
                        'partnershipnumbernotfound',
                        'local_equipment',
                        id
                    )) +
                    '</span>',
            };
        }

        return {
            partnership,
            inStudentSection: true,
        };
    };

    // We can use this for mapping below, instead of the for loop.
    // Grab each family chunk and split each line into its own element within the 'lines' array.
    // const promiseResults = await Promise.all(
    //     family.split('\n').map(async (line) => {
    //         Log.debug('Processing family... mapping...');
    //         line.trim();
    //         const textType = determineTextType(line);

    //         if (textType === 'student') {
    //             inStudentSection = true;
    //         }

    //         // Determine whether we're in the student section, parent section, or an unknown section.
    //         if (textType === 'unknown') {
    //             const errorString = await getString(
    //                 'unrecognizedformat',
    //                 'local_equipment',
    //                 line
    //             );
    //             familyHTML.push(
    //                 `<span class="pl-2 alert-danger">${errorString}</span>`
    //             );
    //             // The 'familyData' object won't need anything added, 'cause we're checking for errors using 'alert-danger'.
    //         } else if (partnershipAdded && textType === 'partnership') {
    //             const errorString = await getString(
    //                 'connotaddmorethanonepartnership',
    //                 'local_equipment',
    //                 line
    //             );
    //             familyHTML.push(
    //                 `<span class="pl-2 alert-danger">${errorString}</span>`
    //             );
    //         } else if (textType === 'partnership') {
    //             ({ partnership, inStudentSection } =
    //                 await processPartnershipInfo(line));
    //             familyHTML.push(partnership.html);
    //             // There can only be one partnership
    //             familyData.partnership = partnership.data;
    //             partnershipAdded = true;
    //         } else if (!inStudentSection) {
    //             // We haven't entered the student section yet.
    //             ({ parent } = await processParentInfo(
    //                 line,
    //                 textType,
    //                 parent
    //             ));

    //             // The 'email' line marks the end of a given parent, so we push the parent object to the 'parents' array.
    //             if (textType === 'email') {
    //                 parents.push(parent);
    //                 parent = {};
    //             }
    //             familyHTML.push(parent[textType].html);
    //         } else {
    //             // Now we are in the student section.
    //             ({ student } = await processStudentInfo(
    //                 line,
    //                 textType,
    //                 student
    //             ));
    //             // line = student[textType].html;
    //             if (textType === 'courses') {
    //                 students.push(student);
    //                 student = {};
    //             }
    //             familyHTML.push(student[textType].html);
    //         }

    //         // We're using this map function to change and update the variables above, so we don't need to return anything.
    //     })
    // );

    /**
     * Process a single family's data, a.k.a. a family chunk.
     * This const will be used as input for the map() function below.
     *
     * @param {string} family - The raw family data string.
     * @return {string} HTML feedback for the family.
     */
    const processFamily = async (family) => {
        try {
            let parents = [];
            let students = [];
            let parent = {};
            let student = {};
            let partnership = {};
            let inStudentSection = false;
            let familyHTML = [];
            let partnershipAdded = false;

            const lines = family
                .split('\n')
                .map((line) => line.trim())
                .filter((line) => line); // Remove empty lines

            for (const line of lines) {
                try {
                    const textType = determineTextType(line);
                    Log.debug(`Processing line: ${line} of type: ${textType}`);

                    if (textType === 'student') {
                        inStudentSection = true;
                    }

                    if (textType === 'unknown') {
                        const errorString = await getString(
                            'unrecognizedformat',
                            'local_equipment',
                            line
                        );
                        familyHTML.push(
                            `<span class="pl-2 alert-danger">${errorString}</span>`
                        );
                        continue;
                    }

                    if (partnershipAdded && textType === 'partnership') {
                        const errorString = await getString(
                            'connotaddmorethanonepartnership',
                            'local_equipment',
                            line
                        );
                        familyHTML.push(
                            `<span class="pl-2 alert-danger">${errorString}</span>`
                        );
                        continue;
                    }

                    switch (true) {
                        case textType === 'partnership': {
                            const result = await processPartnershipInfo(line);
                            partnership = result.partnership;
                            inStudentSection = result.inStudentSection;
                            familyHTML.push(partnership.html);
                            partnershipAdded = true;
                            break;
                        }
                        case !inStudentSection: {
                            parent = await processParentInfo(
                                line,
                                textType,
                                parent
                            );
                            if (parent[textType]) {
                                familyHTML.push(parent[textType].html);
                            }
                            if (textType === 'email') {
                                parents.push({ ...parent }); // Create a deep copy
                                parent = {};
                            }
                            break;
                        }
                        case inStudentSection: {
                            const result = await processStudentInfo(
                                line,
                                textType,
                                student
                            );
                            student = result;
                            if (student[textType]) {
                                familyHTML.push(student[textType].html);
                            }
                            if (textType === 'courses') {
                                students.push({ ...student }); // Create a deep copy
                                student = {};
                            }
                            break;
                        }
                    }
                } catch (lineError) {
                    Log.error('Error processing line:', line, lineError);
                    const errorString = await getString(
                        'errorprocessingline',
                        'local_equipment',
                        line
                    );
                    familyHTML.push(
                        `<span class="pl-2 alert-danger">${errorString}</span>`
                    );
                }
            }

            // Handle any remaining student data
            if (Object.keys(student).length > 0) {
                students.push({ ...student });
            }

            const familyData = { parents, students, partnership };
            const htmlOutput = `<div class="bg-light border p-3">${familyHTML.join(
                '<br />'
            )}</div>`;

            return {
                data: familyData,
                html: htmlOutput,
            };
        } catch (error) {
            Log.error('Error processing family:', error);
            const errorString = await getString(
                'errorprocessingfamily',
                'local_equipment'
            );
            return {
                data: {},
                html: `<div class="bg-light border p-3"><span class="pl-2 alert-danger">${errorString}</span></div>`,
            };
        }
    };

    try {
        const familiesInput = input
            .split('\n\n')
            .filter((family) => family.trim());
        const results = await Promise.all(familiesInput.map(processFamily));

        const familiesData = results
            .map((result) => result.data)
            .filter(Boolean);
        const familiesHTML = results.map((result) => result.html);

        Log.debug('Processing complete:', {
            familiesCount: familiesData.length,
            dataStructure: familiesData,
            htmlContent: familiesHTML,
        });

        return {
            data: familiesData,
            html: familiesHTML.join('<br>'),
        };
    } catch (error) {
        Log.error('Error in validateFamilyData:', error);
        const errorMessage = await getString(
            'errorvalidatingfamilydata',
            'local_equipment'
        );
        return {
            data: [],
            html: `<div class="alert alert-danger">${errorMessage}</div>`,
        };
    }
};
