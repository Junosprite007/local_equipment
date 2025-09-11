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

/**
 * Initialize the module.
 */
export const init = () => {
    // Core form elements.
    const $textarea = $('#id_familiesinputdata');
    const $preprocessDiv = $('#id_familypreprocessdisplay');
    const $preprocessButton = $('.preprocessbutton');
    const $shownexterrorContainer = $('.shownexterror-container');
    const $noerrorsfoundContainer = $('.noerrorsfound-container');
    const $shownexterrorButton = $shownexterrorContainer.find('button');
    const $noerrorsfoundButton = $noerrorsfoundContainer.find('button');
    const $submitButton = $('#id_submitbutton');
    const $partnershipData = $('#id_partnershipdata');
    const $courseData = $('#id_coursesthisyear');
    const $familiesDataInput = $('#id_familiesdata');

    // Parse initial data.
    const partnershipDataValue = JSON.parse(
        $partnershipData.attr('data-partnerships')
    );
    const courseDataValue = JSON.parse(
        $courseData.attr('data-coursesthisyear')
    );

    // Error navigation state.
    let currentErrorIndex = -1;

    /**
     * Process the current data and update UI.
     */
    const processCurrentData = async () => {
        try {
            const data = {
                input: cleanInputText($textarea.val()),
                partnerships: partnershipDataValue,
                courses: courseDataValue,
            };

            const families = await validateFamilyData(data);
            Log.debug('families: ');
            Log.debug(families);
            $familiesDataInput.val(JSON.stringify(families.data));

            $preprocessDiv.html(families.html);
            $preprocessDiv.css('height', $textarea.outerHeight() + 'px');

            const hasErrors = families.html.includes('alert-danger');

            // Update form controls based on error state
            $shownexterrorButton.prop('hidden', !hasErrors);
            $shownexterrorButton.prop('disabled', !hasErrors);
            $noerrorsfoundButton.prop('hidden', hasErrors);
            $submitButton.prop('disabled', hasErrors);
            $shownexterrorButton.prop({
                disabled: !hasErrors,
                hidden: !hasErrors,
            });

            // Automatically scroll to first error
            if (hasErrors && currentErrorIndex === -1) {
                currentErrorIndex = 0;
                scrollToError(currentErrorIndex);
            }
        } catch (error) {
            Log.error('Error in preprocessing:', error);
            $submitButton.prop('disabled', true);

            const errorMessage = await getString(
                'errorvalidatingfamilydata',
                'local_equipment'
            );

            $preprocessDiv.html(
                `<div class="alert alert-danger">${errorMessage}</div>`
            );
        }
    };

    // Set initial height and handlers
    $preprocessDiv.css('height', $textarea.outerHeight() + 'px');

    $textarea.on('input', () => {
        $submitButton.prop('disabled', true);
    });

    // Function to clean up input text
    const cleanInputText = (text) => {
        if (!text || typeof text !== 'string') {
            return '';
        }
        return text.replace(/^\s+|\s+$/g, '');
    };

    /**
     * Get the corresponding line in textarea for an error
     * @param {jQuery} $error The error element from preprocess div
     * @returns {Object} Line information including line number and specific error positions
     */
    const getErrorLineInTextarea = ($error) => {
        const errorText = $error.text().trim();
        const lines = $textarea.val().split('\n');

        if (errorText.includes('Course ID')) {
            const courseIdMatch = errorText.match(/Course ID #(.+) not found/);

            if (courseIdMatch) {
                const courseId = courseIdMatch[1];
                const lineIndex = lines.findIndex((line) => {
                    return line.startsWith('**') && line.includes(courseId);
                });

                if (lineIndex !== -1) {
                    const line = lines[lineIndex];
                    const start = line.indexOf(courseId);
                    return {
                        lineNumber: lineIndex,
                        errorStart: start,
                        errorEnd: start + courseId.length,
                    };
                }
            }
        }

        // General error line matching
        const cleanErrorText = errorText
            .replace(/^(Error:|Invalid:|Not found:|Course ID)\s*/i, '')
            .trim();

        const lineIndex = lines.findIndex((line) => {
            const cleanLine = line.trim();
            return (
                cleanLine &&
                (cleanErrorText.includes(cleanLine) ||
                    cleanLine.includes(cleanErrorText))
            );
        });

        return {
            lineNumber: lineIndex,
            errorStart: -1,
            errorEnd: -1,
        };
    };

    /**
     * Scroll element into view
     * @param {Element} element The element to scroll into view
     */
    const smoothScrollIntoView = (element) => {
        const $container = $('#id_familypreprocessdisplay');
        const containerTop = $container.offset().top;
        const elementTop = $(element).offset().top;
        const scroll =
            elementTop -
            containerTop -
            $container.height() / 2 +
            $(element).height();

        $container.scrollTop($container.scrollTop() + scroll);
    };

    /**
     * Highlight specific text in textarea
     * @param {Object} errorInfo A.I generated doc: Line information including line number and specific error positions.
     */
    const highlightTextareaError = (errorInfo) => {
        if (errorInfo.lineNumber === -1) {
            return;
        }

        const lines = $textarea.val().split('\n');
        let position = lines
            .slice(0, errorInfo.lineNumber)
            .reduce((pos, line) => pos + line.length + 1, 0);

        const textareaElement = $textarea.get(0);
        textareaElement.focus();

        if (errorInfo.errorStart >= 0 && errorInfo.errorEnd >= 0) {
            textareaElement.setSelectionRange(
                position + errorInfo.errorStart,
                position + errorInfo.errorEnd
            );
        } else {
            textareaElement.setSelectionRange(
                position,
                position + lines[errorInfo.lineNumber].length
            );
        }

        // Scroll textarea to show highlighted text.
        const lineHeight = parseInt($textarea.css('line-height'));
        const scrollPosition = errorInfo.lineNumber * lineHeight;
        $textarea.scrollTop(scrollPosition - $textarea.height() / 2);
    };

    /**
     * Scroll to and highlight specific error.
     * @param {number} index The index of the error to scroll to.
     */
    const scrollToError = (index) => {
        const $errors = $preprocessDiv.find('.alert-danger');
        if (index >= 0 && index < $errors.length) {
            $('.error-highlight').removeClass('error-highlight');
            const $currentError = $errors.eq(index);
            $currentError.addClass('error-highlight');

            smoothScrollIntoView($currentError.get(0));
            highlightTextareaError(getErrorLineInTextarea($currentError));
        }
    };

    // Add error highlight styling
    $('<style>')
        .text(
            `
            .error-highlight {
                animation: highlight-pulse 1s;
                box-shadow: 0 0 8px rgba(220, 53, 69, 0.5);
            }
            @keyframes highlight-pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.02); }
                100% { transform: scale(1); }
            }
        `
        )
        .appendTo('head');

    // Event Handlers
    $preprocessButton.on('click', async (e) => {
        e.preventDefault();
        $textarea.val(cleanInputText($textarea.val()));
        currentErrorIndex = -1;
        await processCurrentData();
    });

    $shownexterrorButton.on('click', async (e) => {
        e.preventDefault();
        await processCurrentData();
        const $errors = $preprocessDiv.find('.alert-danger');
        if ($errors.length > 0) {
            currentErrorIndex = (currentErrorIndex + 1) % $errors.length;
            scrollToError(currentErrorIndex);
        }
    });

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
    courses = Object.assign({}, ...Object.values(courses));
    if (!input || typeof input !== 'string') {
        throw new Error(
            getString(
                'invalidinput',
                'local_equipment',
                getString('expectedanonemptystring', 'local_equipment')
            )
        );
    }

    // This creates and object with text types as keys and regexes to match as values.
    const regexes = {
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        phone: /^(\+?\d{1,2}\s?)?(\(?\d{3}\)?[\s.-]?)?\d{3}[\s.-]?\d{4}$/,
        // name: /^[a-zA-ZÀ-ÿ\u00f1\u00d1\u0100-\u017f\s'-]+$/,
        name: /^[\p{L}\p{M}\p{N}\p{S}\p{Pd}\s'-]+$/u,
        partnership: /^-?\d+$/,
        student: /^\*(?!\*)(.)/,
        courses: /^\*\*.*$/,
    };

    /**
     * Determine the type of text based on regex patterns. Below, we are defining a function that will take the above oject using
     * and convert it into an array of key/values, so instead of having an object like this:
     *
     * textType1: /regex1/
     * textType2: /regex2/
     * textType3: /regex3/
     * textType4: /regex4/
     *
     * we will have only the one matched key/value pair returned in the form of an array like this:
     *
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
                if (type === 'name') {
                    // We have to add something like this currently because the regex for names is too broad and encapsulates all
                    // partnerships, too, so we'll test again to see if it's also a 'partnership' match (strictly number digits).
                    if (regexes.partnership.test(line)) {
                        return 'partnership';
                    }
                }
                return type;
            }
        }
        return 'unknown';
    };

    /**
     * Parse a full name string into first, middle, and last name components.
     *
     * @param {string} fullName - The full name string to parse
     * @returns {Object} Object containing name components
     */
    const parseName = async (fullName) => {
        // First, clean up the input by removing extra spaces and standardizing whitespace
        const cleanName = fullName.trim().replace(/\s+/g, ' ');

        // Split the name into parts
        const parts = cleanName.split(' ');

        // Initialize the result object
        const result = {
            firstName: '',
            middleName: '',
            lastName: '',
        };

        if (parts.length === 0) {
            return result;
        }

        if (parts.length === 1) {
            // Only one name provided
            result.firstName = parts[0];
            return result;
        }

        if (parts.length === 2) {
            // First and last name only
            result.firstName = parts[0];
            result.lastName = parts[1];
            return result;
        }

        // For three or more parts
        result.firstName = parts[0];
        result.lastName = parts[parts.length - 1];
        result.middleName = parts.slice(1, -1).join(' ');

        return result;
    };

    // Example usage:
    const processPersonName = async (nameString) => {
        const {
            firstName,
            middleName = null,
            lastName,
        } = await parseName(nameString);

        return {
            firstName,
            middleName,
            lastName,
        };
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
                '<span class="ps-2 pe-2 alert-danger">' +
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
        parent = {
            ...parent,
            [textType]: {
                html: line,
                data: line,
            },
        };
        switch (textType) {
            case 'name': {
                let nameHTML = line.trim();
                let names = nameHTML.split(' ');
                if (names.length === 1) {
                    // Only one name provided.
                    nameHTML =
                        '<span class="ps-2 pe-2 alert-danger">' +
                        (await getString(
                            'onlyonenameprovided',
                            'local_equipment',
                            nameHTML
                        )) +
                        '</span>';
                }
                const nameParts = await processPersonName(line);

                parent = {
                    ...parent,
                    [textType]: {
                        data: {
                            firstName: nameParts.firstName,
                            middleName: nameParts.middleName,
                            lastName: nameParts.lastName,
                        },
                        html: nameHTML,
                    },
                };
                break;
            }
            case 'email':
                parent[textType].html =
                    '<span class="ps-4 pe-4">' + line + '</span>';
                break;
            case 'phone': {
                let formattedPhone = await parsePhoneNumber(line);
                parent[textType].data = formattedPhone;
                parent[textType].html =
                    '<span class="ps-4 pe-4">' + formattedPhone + '</span>';
                break;
            }
            default:
                break;
        }
        return parent;
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
                    '<span class="ps-2 pe-2 alert-danger">' +
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

    /**
     * Process student information from a line of text.
     *
     * @param {string} line - The line of text to process.
     * @param {string} textType - The type of text determined.
     * @param {Object} student - The current student object.
     * @param {string} partnership - The partnership object.
     * @param {boolean} partnershipAdded - Whether a partnership has been added.
     * @param {Array} parents - The parent objects.
     * @return {Object} Updated student object.
     */
    const processStudentInfo = async (
        line,
        textType,
        student,
        partnership,
        partnershipAdded,
        parents
    ) => {
        student = {
            ...student,
            [textType]: {
                html: line,
                data: line,
            },
        };
        const parentExists = parents.length > 0;

        switch (textType) {
            case 'student': {
                // This refers to the student's name, which is the only line that is preceded by a single asterisk (*).
                let name = line.replace('*', '').trim();
                let names = name.split(' ');
                const nameParts = await processPersonName(name);

                // All users in Moodle need a first and last name. If only one name is provided for the student, we'll automatically
                // append the first listed parent's last name and warn the admin user about the automated addition; otherwise
                // display an error message.
                if (names.length === 1 && parentExists) {
                    name =
                        name +
                        ' ' +
                        '<span class="ps-1 pe-1 alert-warning">' +
                        parents[0]?.name?.data?.lastName +
                        '</span>';
                } else if (names.length === 1 && !parentExists) {
                    name =
                        '<span class="ps-2 pe-2 alert-danger">' +
                        (await getString(
                            'onlyonenameprovided',
                            'local_equipment',
                            name
                        )) +
                        '</span>';
                }

                student = {
                    ...student,
                    [textType]: {
                        data: {
                            firstName: nameParts.firstName,
                            middleName: nameParts.middleName,
                            lastName: nameParts.lastName,
                        },
                        html: name,
                    },
                };
                break;
            }
            case 'email':
                student[textType].data = line;
                student[textType].html =
                    '<span class="ps-4 pe-4">' + line + '</span>';
                break;
            case 'phone': {
                let formattedPhone = await parsePhoneNumber(line);
                student[textType].data = formattedPhone;
                student[textType].html =
                    '<span class="ps-4 pe-4">' + formattedPhone + '</span>';
                break;
            }
            case 'courses': {
                const coursesData = line
                    .replace('**', '')
                    .trim()
                    .split(',')
                    .map((course) => course.trim());
                const processedCourses = [];

                const coursesHTML = await Promise.all(
                    coursesData.map(async (id) => {
                        const msg = {
                            c_id: id,
                            p_id: partnership.data,
                        };
                        let courseExistsInPartnership = false;
                        const courseAlreadyProcessed =
                            processedCourses.includes(id);
                        let courseName = '';
                        if (partnershipAdded) {
                            courseExistsInPartnership =
                                partnerships[partnership?.data].coursedata[
                                    id
                                ] !== undefined;
                        }
                        if (
                            courses[id] &&
                            !courseAlreadyProcessed &&
                            (!partnershipAdded ||
                                (partnershipAdded && courseExistsInPartnership))
                        ) {
                            // This is the successful case.
                            processedCourses.push(id);
                            // Below utilizes the EN DASH character: '–' or \u2013
                            const enDash = '–';
                            const regex = new RegExp(`${id} ${enDash} `, 'g');
                            courseName = courses[id].replace(regex, '');

                            // Add this block to validate that the students in a given family are taking courses from the same
                            // partnership. The partnership does NOT get added to the student object here, but it does get added to
                            // the family object in the processFamily() function.

                            // TODO: Because of how this currently works, we can't add the partnership to the student object.
                            // Basically, the partnership getting added could be wrong since some partnership use the course from
                            // other partnerships. I'll figure out how to do this later.

                            // if (!partnershipAdded) {
                            //     // Find which partnership this course belongs to
                            //     for (const [pid, pdata] of Object.entries(
                            //         partnerships
                            //     )) {
                            //         if (
                            //             pdata.coursedata &&
                            //             pdata.coursedata[id]
                            //         ) {
                            //             partnership = {
                            //                 data: pid,
                            //                 html: pdata.name,
                            //             };
                            //             partnershipAdded = true;
                            //             break;
                            //         }
                            //     }
                            // }
                        } else if (
                            courses[id] &&
                            courseAlreadyProcessed &&
                            (!partnershipAdded ||
                                (partnershipAdded && courseExistsInPartnership))
                        ) {
                            processedCourses.push(id);
                            const errorMessage = await getString(
                                'coursealreadyadded',
                                'local_equipment',
                                msg.c_id
                            );
                            courseName = `<span class="ps-2 pe-2 alert-danger">${errorMessage}</span>`;
                        } else if (
                            courses[id] &&
                            partnershipAdded &&
                            !courseExistsInPartnership
                        ) {
                            const errorMessage = await getString(
                                'courseidnotfoundinpartnership',
                                'local_equipment',
                                msg
                            );
                            courseName = `<span class="ps-2 pe-2 alert-danger">${errorMessage}</span>`;
                        } else {
                            const errorMessage = await getString(
                                'courseidnotfound',
                                'local_equipment',
                                msg.c_id
                            );
                            courseName = `<span class="ps-2 pe-2 alert-danger">${errorMessage}</span>`;
                        }

                        return courseName;
                    })
                );

                student[textType].data = coursesData;
                student[textType].html =
                    '<span class="ps-4 pe-4">' +
                    coursesHTML.join(', ') +
                    '</span>';
                break;
            }
            default:
                break;
        }
        return student;
    };

    /**
     * Process a single family's data, a.k.a. a family chunk.
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
            let studentName = '';
            let partnership = {};
            let inStudentSection = false;
            let familyHTML = [];
            let partnershipAdded = false;
            let parentNum = 0;
            let studentNum = 0;

            // Trim the shit out of it.
            const lines = family
                .split('\n')
                .map((line) => line.trim())
                .filter((line) => line);

            for (const line of lines) {
                try {
                    const textType = determineTextType(line);

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
                            `<span class="ps-2 alert-danger">${errorString}</span>`
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
                            `<span class="ps-2 alert-danger">${errorString}</span>`
                        );
                        continue;
                    }

                    switch (true) {
                        case textType === 'partnership': {
                            const result = await processPartnershipInfo(line);
                            partnership = result.partnership;
                            familyHTML.push(partnership.html);
                            partnershipAdded = true;
                            break;
                        }
                        // This means we are in the parent section.
                        case !inStudentSection: {
                            if (textType === 'courses') {
                                const errorMessage = await getString(
                                    'thesecoursesneedastudent',
                                    'local_equipment',
                                    line
                                );
                                familyHTML.push(
                                    `<span class="ps-2 alert-danger">${errorMessage}</span>`
                                );
                                break;
                            }
                            parent = await processParentInfo(
                                line,
                                textType,
                                parent
                            );
                            if (textType === 'name') {
                                parents.push({ ...parent });
                            }
                            if (parent[textType]) {
                                familyHTML.push(parent[textType].html);
                            }

                            if (textType === 'email') {
                                parents[parentNum] = { ...parent };
                                parent = {};
                                parentNum++;
                            }

                            break;
                        }
                        case inStudentSection: {
                            const result = await processStudentInfo(
                                line,
                                textType,
                                student,
                                partnership,
                                partnershipAdded,
                                parents
                            );
                            student = result;

                            if (student[textType]) {
                                familyHTML.push(student[textType].html);
                            }
                            if (textType === 'student') {
                                studentName = student[textType].data.firstName;
                                students.push({ ...student });
                            }

                            if (textType === 'courses') {
                                const needsEmail =
                                    parents.length === 0 &&
                                    student.email === undefined &&
                                    studentName !== '';
                                if (needsEmail) {
                                    const errorMessage = await getString(
                                        'studentneedsemail',
                                        'local_equipment',
                                        studentName
                                    );
                                    familyHTML.push(
                                        `<span class="ps-2 alert-danger">${errorMessage}</span>`
                                    );
                                }

                                students[studentNum] = { ...student };
                                student = {};
                                studentNum++;
                            }
                            break;
                        }
                    }
                } catch (lineError) {
                    Log.error('Error processing line:');
                    Log.error(line);
                    Log.error(lineError);
                    const errorString = await getString(
                        'errorprocessingline',
                        'local_equipment',
                        line
                    );
                    familyHTML.push(
                        `<span class="ps-2 alert-danger">${errorString}</span>`
                    );
                }
            }

            // Handle any remaining student data
            if (Object.keys(student).length > 0) {
                students.push({ ...student });
            }

            for (parent of parents) {
                if (parent.email === undefined) {
                    const errorMessage = await getString(
                        'parentneedsemail',
                        'local_equipment',
                        parent.name.data.firstName
                    );
                    Log.debug(errorMessage);
                    familyHTML.unshift(
                        `<span class="ps-2 alert-danger">${errorMessage}</span>`
                    );
                }
            }

            if (parents.length !== parentNum) {
                const errorMessage = await getString(
                    'errorprocessingparents',
                    'local_equipment'
                );
                familyHTML.unshift(
                    `<span class="ps-2 alert-danger">${errorMessage}</span>`
                );
            }

            if (students.length !== studentNum) {
                const errorMessage = await getString(
                    'errorprocessingstudents',
                    'local_equipment'
                );
                familyHTML.unshift(
                    `<span class="ps-2 alert-danger">${errorMessage}</span>`
                );
            }

            // TODO: This is where we should eventually add the partnership to the family based on the
            // partnership that the first course ID belongs to 'cause at this point, we already know that the
            // course exists somewhere.

            // If no partnership specified but courses exist, try to find partnership from first course

            // TODO: Because of how this currently works, we can't add the partnership to the student object. Basically,
            // the partnership getting added could be wrong since some partnership use the course from other
            // partnerships. I'll figure out how to do this later.

            // if (
            //     !partnershipAdded &&
            //     students.length > 0 &&
            //     students[0].courses?.data
            // ) {
            //     const firstCourseId = students[0].courses.data[0];
            //     // Find partnership that contains this course
            //     for (const [pid, pdata] of Object.entries(partnerships)) {
            //         if (pdata.coursedata && pdata.coursedata[firstCourseId]) {
            //             partnership = {
            //                 data: pid,
            //                 html: pdata.name,
            //             };
            //             partnershipAdded = true;
            //             familyHTML.unshift(partnership.html); // Add to start of HTML
            //             break;
            //         }
            //     }
            // }

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
                html: `<div class="bg-light border p-3"><span class="ps-2 alert-danger">${errorString}</span></div>`,
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

        Log.debug('Processing complete:');
        Log.debug({
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

/**
 * Rotate the symbol on click.
 *
 */
export const rotateSymbol = () => {
    const headers = document.querySelectorAll(
        '.local-equipment-notification-header'
    );
    headers.forEach((header) => {
        header.addEventListener('click', () => {
            const expanded = header.getAttribute('aria-expanded') === 'true';
            header.setAttribute('aria-expanded', !expanded);
        });
    });
};
