# Shinano Project

## Introduction

Welcome to the Shinano Project. Our platform is designed to facilitate direct
connections between those initiating projects and seeking collaborative partners,
and between employers and contractors looking for opportunities. We are committed
to fostering autonomous, cooperative, and sustainable work practices, with the goal
of spreading the principles of worker cooperatives and the solidarity economy in
society.

## Purpose of Shinano Software and Web Service

The Shinano software and web service are tailored to address both sides of the
collaborative and employment spectrum. For individuals or groups with new ideas or
specific projects, it offers a platform to post their intentions and find
enthusiastic collaborators. Simultaneously, it provides a venue for contractors
seeking employment opportunities and employers looking for skilled contractors.
This system is dedicated to encouraging collaborative and equitable economic
activities by entirely eliminating intermediaries and margins. Our aim is to
ensure open and equal opportunities for all participants, fostering an environment
of equal contribution and participation in projects and decision-making processes.

## Organizational Structure & Policy

The Shinano Project and its system are currently established and managed by the
TNG Workers' Cooperative in Yugawara, Japan. The future goal is to evolve into a
more democratic structure, forming an organization collectively with its users.
Initially, the system is offered completely free of charge. TNG Cooperative
finds significant value in the system for its own use, and this motivates the
decision to offer it for free. Additionally, the experience gained in system
development is used as a reference to promote the cooperative itself. The
matching system primarily serves as a replacement for traditional phone
communication and does not engage in any individual labor or other contract
negotiations. These contracts should be directly managed between the involved
parties. However, if postings not in line with the platform's goals are found,
the operating body, adhering to our democratic principles, may reach out to
those involved for appropriate discussion and resolution.

## はじめに

信濃プロジェクトへようこそ。起業・企画の発案者と参加者、または雇用者と請負業者の間の
直接的なマッチングを促進することに注力している当プラットフォームは、自律的、協調的、持続的な
働き方を促進し、労働者協同組合と連帯経済の理念を社会に広めることを目指しています。

## 信濃プラットフォームの目的

信濃のソフトウェアとウェブサービスは、新しいアイデアや特定のプロジェクトのための人材を求める人々に対し、
その意向を投稿し、関心を持つ当事者が応募や参加できる共同事業と請負業者のマッチングシステムです。
このシステムは、中間業者やマージンをゼロにすることを奨励し、協力的で公正な経済活動を促進します。
また、すべての人に開かれた平等な機会の提供を目指しています。参加者は、プロジェクトや意思決定において
平等な貢献と参加が奨励されます。

## 運営母体・運営方針

信濃プロジエクトとそのシステムは、神奈川県湯河原町にあるTNG労働者協同組合が当初設立・運営をしていますが、将来的にはシステムの利用者と団体を形成し、より民主的な運営に移行する方針です。システムの利用料金は少なくとも当初は完全に無料で、その理由は我々TNG組合も参加者として自己の利用価値を見出しているのと、システム開発の経験そのものを組合の営業に活かしたいからです。
当マッチングシステムはあくまでも「電話の代わり」であり、個々の労働・その他契約などに一切関与しません。契約は当事者同士でお願いします。
ただし、本プラットフォームの目的にそぐわない掲示などがあった場合は、お知らせください。また、運営体からご連絡させていただく可能性もございます。

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
   - To start the PHP server, navigate to the document index at './studyyard' and use the following command:
     ```
     $ php -S localhost:5000 -t ./studyyard/ -a
     ```
   - Access localhost:5000 to view content and interact with PHP request handlers.

2. **SQL**:
   - [Details about SQL usage and commands]

3. **Deployment Server**:
   - [Instructions for server deployment]

## Branch Information

| Name    | Purpose                                              |
| ------- | ---------------------------------------------------- |
| main    | Current stable software version and its source code. |
| develop | Branch used for development.                         |

## Feedback and Contributions

We encourage users to provide feedback and suggestions for improvements. Your input is valuable in refining and enhancing the Shinano software and its supporting elements.