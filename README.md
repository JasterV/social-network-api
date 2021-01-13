# Welcome to social-network 👋

![Version](https://img.shields.io/badge/version-0.9.0-blue.svg?cacheSeconds=2592000)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](#)

> Rest API build with Laravel that can be used as the base for a social network backend

## Pre Requisites

- Composer
- Docker && Docker Compose
- Preferible a Linux or Mac, thx :)

## Server configure

- Copy the .env.example to a .env file
- execute:

```bash
> make setup_project
```

## Server run

```bash
> make run # It may take a while the first time, be patient!
```

## Run tests

```bash
> make tests_local_run
```

If you want to run an specific test you can execute the following:

```bash
> make tests_local_run ARGS="--filter <test-name>"
```

## Server poweroff

```bash
> make down # Remember to execute this once you are done, otherwise docker will keep consuming resources on your machine!
```

## 👤 Contributors ✨

<table>
<tr>    
<td align="center"><a href="https://github.com/JasterV"><img src="https://avatars3.githubusercontent.com/u/49537445?v=4" width="100" alt=""/><br /><sub><b>Victor Martínez</b></sub></a></td>
</tr>
</table>

## Show your support

Give a ⭐️ if this project helped you!

---

_This README was generated with ❤️ by [readme-md-generator](https://github.com/kefranabg/readme-md-generator)_
