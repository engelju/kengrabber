# KenGrabber
A tool for generating podcast minipages out of youtube channels.

It's an php commandline application that grabs an

## 1. Dependencies
* php > 5.5 (with CLI support)
* youtube-dl

## 2. Installation
TDB

## 3. Usage
You need to make the "kengrabber.phar" executable: ```chmod +x kengrabber.phar```.

On the first run, all necessairy directories will be created.

### 3.1. Configuration
The application starts with some test parameters. So you need to configure it either with the configuration wizard or manually. The configuration file will be automatically created when you start kengrabber the first time.

To start the wizard: ```kengrabber.phar configure```

__Configuration parameters:__

```
youtube_api_key: string [Your youtube API key]
youtube_channel_username: string [Username of channel you want to grab]
youtube_queries: array [Things you want to grab on this page (in wizard semikolon separated)]
web_url: string [url where you kengrabber instance is available]
```

### 3.2. Exposing kengrabber to the web
Kengrabber automatically creates a directory ```web```. You only need to put this under a domain and it should be available.

To start rendering enter: ```kengrabber.phar render```

### 3.3. Build all
```kengrabber.phar build```

## 4. Documentation for developers
### 4.1. git-flow
This repo uses git-flow, so master are always stable releases. Development has to made in the develop-branch with additional subbranches.

See [nvie.com/posts/a-successful-git-branching-model/](http://nvie.com/posts/a-successful-git-branching-model/)

### 4.2. Additional Dependencies for building
* composer ([getcomposer.org](http://getcomposer.org))
* nodejs/npm ([nodejs.org](http://nodejs.org))
* bower ([bower.io](http://bower.io))
* gulp ([gulpjs.org](http://gulpjs.org))
* box ([box-project.org](http://box-project.org))

### 4.3. Create building environment

1. Clone this repo
2. ```npm install```
3. ```bower install```
4. ```composer install```
5. ```gulp watch```

### 4.4. Usage without .phar
```php  kengrabber.php [command]```


### 4.5. Building the .phar file
Building is relatively easily, because we are using the box project.

Settings: "box.json"

1. ```box build```
2. wait...

Afterwards you have your .phar file and all should be done.