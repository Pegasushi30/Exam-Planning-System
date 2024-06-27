# Exam Planning System

## Overview

The Exam Planning System is designed to streamline the management of exams within a university setting. The system encompasses various entities and user roles, each with distinct responsibilities to ensure efficient scheduling and oversight of exams.

### Main Entities

- **Employee**: Represents individuals involved in the exam process.
- **Department**: Represents academic departments within the university.
- **Faculty**: Represents faculties within the university.
- **Courses**: Represents individual courses offered by the university.
- **Exam**: Represents scheduled exams.

## User Roles and Responsibilities

### Assistants
- **Proctor Exams**: Responsible for invigilating exams.
- **View Weekly Program**: Displayed in table format.
- **Select Courses**: Assistants can select courses they will attend, marking their unavailability for exams during those times.

### Secretaries
- **Create Exams**: Enter exam details such as name, date, time, and number of required classes.
- **Insert Course Information**: Add courses related to their department.
- **Match Assistants**: Assign assistants to exams fairly, ensuring no scheduling conflicts.

### Head of Department
- **Oversee Exam Schedule**: View the entire exam schedule of the department.
- **View Assistant Workload Reports**: Monitor the distribution of workloads among assistants.

### Head of Secretary
- **Create Faculty Exams**: Similar to secretaries, but at the faculty level.
- **Insert Faculty Course Information**: Add courses for the entire faculty.
- **Assign Faculty Assistants**: Match assistants across all departments under the faculty to exams.

### Dean
- **Oversee Faculty Exam Schedule**: View the entire exam schedule for the whole faculty.

## Detailed Functionalities

### Login Page
- **Authentication**: Users log in with a username and password.
- **Forgot Password**: Functionality to recover forgotten passwords.
- **Welcome Message**: Display a personalized welcome message with the user's name.

### Assistant Page
- **Course Selection**: Drop-down menus to select registered courses.
- **Weekly Plan Display**: A table showing the assistant's weekly plan, autofilled with courses and exams.
- **Refresh Button**: Update the weekly plan display.

### Secretary Page
- **Course and Exam Input**: Drop-down menus for course selection, and input fields for exam details.
- **Assistant Assignment**: List of least-scored assistants available for the exam, ensuring no schedule conflicts.
- **Assistant Score Display**: List all assistants' scores.

### Head of Department Page
- **Exam Schedule Display**: List of exams in ascending order by date and time.
- **Workload Table**: Display assistant names and their workload percentages.

### Head of Secretary Page
- **Faculty Course and Exam Management**: Similar to the secretary page but for the entire faculty.
- **Assign Faculty Assistants**: Assign assistants across all departments.

### Dean Page
- **Department Exam List**: Drop-down menu to select a department and view its exam schedule.
