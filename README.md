#Dev Container (not ready)

This is only developer environment package.

Provide fast way to create dev-environment in container with LAMP and other project stuff.

#Install

Add to composer

    "require-dev": {
        "ivan1986/dev-container": "*",
    },

or

    composer require ivan1986/dev-container --dev

run

    vendor/bin/container init

for first time configure project and select roles, after than you can change roles in `dev-container` folder.

#Usage

Run `vendor/bin/container`

You can `up` `rebuild` and `destroy` container and run `ansible` for update provision.

##DNS

Container name get from composer.json - project name. Install `libnss-docker` for auto-resolve hosts.

If you need custom DNS for big project install `libnss-resolver` and activate role `dns`. (not implemented yet)
in composer extra `resolver` set to domain or array of domains for resolve to container.

##Ansible

Ansible use two `requirements.yml` files:

- `dev-container/roles/requirements.yml`
- `vendor/ivan1986/dev-container/ansible/roles/requirements.yml`

and install roles from galaxy.

Configure you project specific roles in `dev-container/playbook.yml` file. Roles store in:

- `dev-container/roles` - you own roles
- `dev-container/roles/community` - you install from `dev-container/roles/requirements.yml`
- `vendor/ivan1986/dev-container/ansible/roles` - ready roles
- `vendor/ivan1986/dev-container/ansible/roles/community`

