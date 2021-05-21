```
ForkCMS
├── .github
│   ├── ISSUE_TEMPLATE
│   ├── PULL_REQUEST_TEMPLATE
│   └── workflows
├── bin
│   ├── ...
│   ├── console
│   └── ...
├── config
│   ├── packages
│   │   ├── dev
│   │   ├── install
│   │   ├── prod
│   │   ├── test
│   │   └── test_install
│   └── routes
│       ├── dev
│       ├── install
│       ├── prod
│       ├── test
│       └── test_install
├── migrations
├── node_modules
├── public
│   ├── build
│   │   ├── backend
│   │   │   ├── images
│   │   │   └── ...
│   │   ├── frontend
│   │   │   ├── images
│   │   │   └── ...
│   │   └── install
│   │       ├── images
│   │       └── ...
│   ├── uploads
│   │   ├── ...
│   │   ├── Profiles
│   │   │   ├── Avatar
│   │   │   │   ├── 32x32
│   │   │   │   ├── ...
│   │   │   │   └── source
│   │   │   └── ...
│   │   └── ...
│   ├── index.php
│   └── .htaccess => main htaccess file
├── src
│   ├── Core
│   │   ├── assets
│   │   │   ├── images
│   │   │   │   ├── Backend
│   │   │   │   ├── Common
│   │   │   │   ├── Frontend
│   │   │   │   └── Installer
│   │   │   ├── installer
│   │   │   │   └── locale.xml
│   │   │   ├── js
│   │   │   │   ├── Backend
│   │   │   │   ├── Common
│   │   │   │   ├── Frontend
│   │   │   │   └── Installer
│   │   │   └── sass
│   │   │       ├── Backend
│   │   │       ├── Common
│   │   │       ├── Frontend
│   │   │       └── Installer
│   │   ├── Backend
│   │   │   ├── Ajax
│   │   │   ├── Console
│   │   │   ├── Controller
│   │   │   ├── Domain? => Is this the correct name? This would contain same type of things as common but backend specific
│   │   │   ├── Helper => legacy, just for the model class, the old Engine directory
│   │   │   └── Init.php
│   │   ├── config
│   │   ├── Common
│   │   │   ├── ...
│   │   │   ├── Form
│   │   │   ├── Header
│   │   │   └── ...
│   │   ├── DependencyInjection
│   │   ├── Frontend
│   │   │   ├── Ajax
│   │   │   ├── Console
│   │   │   ├── Controller
│   │   │   ├── Domain? => Is this the correct name? This would contain same type of things as common but frontend specific
│   │   │   ├── Helper => legacy, just for the model class, the old Engine directory
│   │   │   └── Init.php
│   │   ├── Installer
│   │   │   ├── Console
│   │   │   ├── Controller
│   │   │   └── Domain? => Is this the correct name? This would contain same type of things as common but installer specific
│   │   ├── templates
│   │   │   ├── Backend
│   │   │   ├── Common
│   │   │   ├── Frontend
│   │   │   └── Installer
│   │   └── Tests
│   │       ├── Backend
│   │       ├── Common
│   │       ├── DataFixtures
│   │       ├── Frontend
│   │       └── Installer
│   ├── Modules
│   │   ├── ...
│   │   ├── Blog
│   │   │   ├── assets
│   │   │   │   ├── images
│   │   │   │   │   ├── Backend
│   │   │   │   │   ├── Common
│   │   │   │   │   └── Frontend
│   │   │   │   ├── installer
│   │   │   │   │   └── locale.xml
│   │   │   │   ├── js
│   │   │   │   │   ├── Backend
│   │   │   │   │   ├── Common
│   │   │   │   │   └── Frontend
│   │   │   │   └── sass
│   │   │   │       ├── Backend
│   │   │   │       ├── Common
│   │   │   │       └── Frontend
│   │   │   ├── Backend
│   │   │   │   ├── Actions
│   │   │   │   ├── Ajax
│   │   │   │   ├── Domain? => Is this the correct name for backend domain specific stuff?
│   │   │   │   ├── Helper => legacy, just for the model class, the old Engine directory
│   │   │   │   ├── Widgets
│   │   │   │   └── Config.php
│   │   │   ├── config
│   │   │   ├── Console
│   │   │   ├── DependencyInjection
│   │   │   ├── Domain? => Or should we do Common/Domain or just Common?
│   │   │   │   ├── ...
│   │   │   │   ├── Post
│   │   │   │   │   ├── ...
│   │   │   │   │   ├── Post.php
│   │   │   │   │   ├── PostRepository.php
│   │   │   │   │   ├── PostTitle.php
│   │   │   │   │   ├── PostType.php
│   │   │   │   │   └── ...
│   │   │   │   ├── Category
│   │   │   │   └── ...
│   │   │   ├── Frontend
│   │   │   │   ├── Actions
│   │   │   │   ├── Ajax
│   │   │   │   ├── Domain? => Is this the correct name for frontend domain specific stuff?
│   │   │   │   ├── Helper => legacy, just for the model class, the old Engine directory
│   │   │   │   ├── Widgets
│   │   │   │   └── Config.php
│   │   │   ├── Installer
│   │   │   ├── templates
│   │   │   │   ├── Backend
│   │   │   │   ├── Common
│   │   │   │   └── Frontend
│   │   │   ├── Tests
│   │   │   │   ├── Backend
│   │   │   │   ├── Domain
│   │   │   │   ├── DataFixtures
│   │   │   │   └── Frontend
│   │   │   └── info.xml
│   │   └── ...
│   ├── Themes
│   │   ├── ...
│   │   ├── ForkCMS
│   │   │   ├── assets
│   │   │   │   ├── images
│   │   │   │   │   ├── Backend
│   │   │   │   │   ├── Common
│   │   │   │   │   └── Frontend
│   │   │   │   ├── js
│   │   │   │   │   ├── Backend
│   │   │   │   │   ├── Common
│   │   │   │   │   └── Frontend
│   │   │   │   └── sass
│   │   │   │       ├── Backend
│   │   │   │       ├── Common
│   │   │   │       └── Frontend
│   │   │   ├── templates
│   │   │   │   ├── Core
│   │   │   │   │   ├── Backend
│   │   │   │   │   ├── Common
│   │   │   │   │   └── Frontend
│   │   │   │   └── Modules
│   │   │   │       ├── ...
│   │   │   │       ├── Blog
│   │   │   │       │   ├── Backend
│   │   │   │       │   ├── Common
│   │   │   │       │   └── Frontend
│   │   │   │       └── ...
│   │   │   └── info.xml
│   │   └── ...
│   ├── bootstrap.php
│   └── kernel.php
├── var
│   ├── cache
│   ├── docs
│   ├── docker
│   └── log
├── vendor
└── .htaccess => for moving working directory to public
```
