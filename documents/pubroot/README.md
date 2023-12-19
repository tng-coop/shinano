

this is the document of commit id :xxxxx .  
this content will be old after newer version.


# Purpose of pubroot

`pubroot` is directory opened to to Internet. its word is omittion of `public_root`.



# Structure of pubroot


### structure

```
$ tree pubroot/
pubroot/
├── account
│   ├── create.php
│   ├── create_pre.php
│   ├── login.php
│   └── logout.php
├── bulletin_board.php
├── bulletin.php
├── cmenu
│   ├── bulletin_edit.php
│   ├── bulletin_new.php
│   ├── bulletins.php
│   ├── bulletin_swap_open_close.php
│   └── index.php
├── cooperator.php
├── cooperators.php
├── decorations
│   └── style.css
├── _index_old.html
└── index.php
```

### path and purpose of each file

| path                               | description                                                                        |
|------------------------------------|------------------------------------------------------------------------------------|
| index.php                          | is the index file.                                                                 |
| cooperator.php                     | shows puid (public_uid) tied cooperator information in detail.                     |
| cooperators.php                    | shows overviews of cooperators by order. it is also able to search.                |
| bulletin.php                       | shows id (of job_entry) tied bulletin (job_entry) information in detail.           |
| bulletin-board.php                 | shows overviews os bulletin by order. at is also able to search.                   |
| account/                           | is directory for login and new cooperator (user) management.                       |
| decorations/                       | is directory, it stores asset files, such as CSS/commonly Images/JavaScripts/etc.  |
| cmenu/                             | is directory of cooperator menu, logged user in session can be access them.        |
| cmenu/index.php                    | is index file of cooperator menu.                                                  |
| cmenu/bulletin_new.php             | to make new bulletin.                                                              |
| cmenu/bulletin_edit.php            | to edit bulletin content and its status.                                           |
| cmenu/bulletins.php                | lists bulletins of logged in cooperator.                                           |
|                                    | and also, it shows cooperator's information, there is editor of cooperator's note. |
| cmenu/bulletin_swap_open_close.php | is called by bulletins.php. it swaps open_close statement.                       |
| _index_old.html                    | is hidden memorial-monument for me. if it is not needed, please delete it.         |




### referenced

- DataBase book (note: PostgreSQL), which also describes PHP webpage.  
  https://www.shoeisha.co.jp/book/detail/9784798164090
- email authorization  
  https://note.com/koushikagawa/n/n9c6e396e2687
