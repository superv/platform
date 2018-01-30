#!/bin/bash

if [ ! -d workbench ]; then
    echo "workbench directory not found.. exiting.."
	exit 0;
fi

DIR=`php -r "echo __DIR__;"`
PACKAGE_DIR=${DIR}'/vendor/superv/platform'
WORKBENCH_DIR=${DIR}'/workbench/superv/platform'

echo $PACKAGE_DIR;

# if package is installed,
# and it is not a Git repo!
if [ -d ${PACKAGE_DIR} ] && [ ! -d ${PACKAGE_DIR}/.git ]; then
    echo "removing ${PACKAGE_DIR}"
    rm -Rf ${PACKAGE_DIR}
fi

# if package dir is removed above or does not exist
# link workbench to package dir
if [ -d ${WORKBENCH_DIR} ] && [ ! -d ${PACKAGE_DIR} ]; then
    echo "linking ${WORKBENCH_DIR} to ${PACKAGE_DIR}"
    ln -s ${WORKBENCH_DIR} ${PACKAGE_DIR}
fi