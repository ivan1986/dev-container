#!/usr/bin/env bash

NAME=$1

PATH_USER=$NAME/dev-container
PATH_PACK=$NAME/vendor/ivan1986/dev-container/ansible

[ -f $PATH_PACK/roles/requirements.yml ] && ansible-galaxy install -v -r $PATH_PACK/roles/requirements.yml -p $PATH_PACK/roles/community/
[ -f $PATH_USER/roles/requirements.yml ] && ansible-galaxy install -v -r $PATH_USER/roles/requirements.yml -p $PATH_USER/roles/community/

ansible-playbook $PATH_USER/playbook.yml
