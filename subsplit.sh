#!/usr/bin/env bash

git subsplit init git@github.com:superv/superv-mono.git
git subsplit update
git subsplit publish --heads="master" __workbench__/superv/platform:git@github.com:superv/platform.git

rm -Rf .subsplit