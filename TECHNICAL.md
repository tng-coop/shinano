## Technical Dependencies and Standards

Our software relies on the following technologies and standards:

1. **HTTP Server**: Essential for handling web requests.

2. **PHP (Version 8.2.13)**:
   - Utilized for server-side application development.
   - Currently in PHP, with plans to transition to Haskell for enhanced functionality.

3. **MySQL (Version XX.XX)**:
   - Our chosen database management system.
   - Reliable and efficient for handling data requirements.

4. **HTML + CSS**:
   - Follows MDN standards ([HTML](https://developer.mozilla.org/en-US/docs/Web/HTML) and [CSS](https://developer.mozilla.org/en-US/docs/Web/CSS)).
   - Essential for designing a responsive and visually appealing user interface.

5. **Javascript**:
   - Adheres to the standard of XXXX (Link).
   - Primarily used for browser-completed animations.
   - Not utilized for server communication or content text calculations.

The front-end is designed to be wget friendly and compatible with text-based web browsers, with a static approach.

## Usage

1. **PHP**:
   - To start the PHP server, navigate to the document index at './pubroot' and use the following command:
     ```
     $ php -S localhost:5000 -t ./pubroot/ -a
     ```
   - Access localhost:5000 to view content and interact with PHP request handlers.

2. **DataBase**:
   - MySQL
   - to do reset and prepare (develop version of) DataBase.
     `$ ./DB-model/reset-dev-database.sh --arguments --needed --to-be-written `
   - such as the part of the user information, if access/write is denied by DataBase, 
     please check authority for your accessing DB user.

3. **Deployment Server**:
   - [Instructions for server deployment]

## Branch Information

| Name       | Purpose                                 |
|------------|-----------------------------------------|
| main       | Developing version and its source code. |

Specific tag and release represents production (stable) version.


## Feedback and Contributions

We encourage users to provide feedback and suggestions for improvements. Your input is valuable in refining and enhancing the Shinano software and its supporting elements.

#### Converting This Document to PDF

To convert markdown documents to PDF, use the `md-to-pdf` command. Run the following command in your terminal:

```bash
npx md-to-pdf
```

When you run the command, you can expect the following output:

```bash
nodePath: /home/yasu/co/shinano/node_modules/md-mermaid-to-pdf/lib
mermaidCode: 3524456
is mermaid
is mermaid
  ✔ generating PDF from README.md
```

[NPX command above comes with NPM](https://nodejs.org).
