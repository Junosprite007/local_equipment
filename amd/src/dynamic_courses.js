export const init = () => {
    const courseData = JSON.parse(
        document.querySelector('input[name="course_data"]').value
    );

    const updateCoursesForPartnership = (partnershipId) => {
        document.querySelectorAll('.partnership-courses').forEach((select) => {
            select.innerHTML = '';

            if (courseData[partnershipId]) {
                Object.entries(courseData[partnershipId]).forEach(
                    ([courseId, courseName]) => {
                        const option = document.createElement('option');
                        option.value = courseId;
                        option.textContent = courseName;
                        select.appendChild(option);
                    }
                );
            }
        });
    };

    const partnershipSelect = document.getElementById('id_partnership');
    partnershipSelect.addEventListener('change', (event) => {
        updateCoursesForPartnership(event.target.value);
    });

    // Initial update
    updateCoursesForPartnership(partnershipSelect.value);
};
