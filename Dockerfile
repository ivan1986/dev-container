# alelindq/ubuntu-systemd
FROM ubuntu:16.04

ENV container docker
ENV DEBIAN_FRONTEND noninteractive

RUN find /etc/systemd/system \
         /lib/systemd/system \
         -path '*.wants/*' \
         -not -name '*journald*' \
         -not -name '*systemd-tmpfiles*' \
         -not -name '*systemd-user-sessions*' \
         -exec rm \{} \;

RUN systemctl set-default multi-user.target

VOLUME ["/sys/fs/cgroup"]
VOLUME ["/run"]

ENTRYPOINT ["/sbin/init"]

ENV LC_ALL C

# config apt to latest ubuntu
RUN echo 'APT::Install-Recommends "1"; \n\
APT::Get::Assume-Yes "true"; \n\
APT::Get::force-yes "true"; \n\
APT::Install-Suggests "0";' > /etc/apt/apt.conf.d/01buildconfig

RUN mkdir -p  /etc/apt/sources.list.d/ && \
    #echo "deb mirror://mirrors.ubuntu.com/mirrors.txt devel main restricted universe multiverse " \ - https://bugs.launchpad.net/ubuntu/+source/apt/+bug/1613184
    echo "deb http://archive.ubuntu.com/ubuntu/ devel main restricted universe multiverse " \
    > /etc/apt/sources.list.d/ubuntu-mirrors.list

# soft
RUN apt-get update && apt-get upgrade && apt-get install -y \
    sudo \
    openssh-server \
    net-tools \
    iputils-ping \
    iproute \
    ansible \
    && rm -rf /var/lib/apt/lists/*
# create web user with sudo
RUN echo 'localhost ansible_connection=local' > /etc/ansible/hosts && \
    useradd --create-home -d /srv/web -s /bin/bash web && \
    echo -n 'web:web' | chpasswd && \
    mkdir -p /etc/sudoers.d && echo "web ALL= NOPASSWD: ALL" > /etc/sudoers.d/web && chmod 0440 /etc/sudoers.d/web

# docker build --rm=true -t <name> .
# sudo docker run -d --privileged -v /sys/fs/cgroup:/sys/fs/cgroup:ro -v `pwd`:/srv/web/<folder> --name=<name> <image>
