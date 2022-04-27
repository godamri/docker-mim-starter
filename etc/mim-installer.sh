#!/bin/bash

mkdir -p /opt/mim
cd /opt/mim

wget http://getmim.github.io/tools/installer.php

VAR=$(expect -c '
  proc abort {} {
    puts "Timeout or EOF\n"
    exit 1
  }
  spawn php installer.php
  expect {
    "Would you like me to add the mim to your `/usr/bin` dir? (Y/n):" { send "n\r" }
    default abort
  }
  expect {
    "Would you like me to add autocompletet to your system? (Y/n):" { send "n\r" }
    default abort
  }
  expect {
    "What is your terminal app? ([bash]/zsh):" { send "bash\r" }
    default abort
  }
  expect {
    "Where to put the autocompleter? (/etc/bash_completion.d):" { send "/etc/bash_completion.d\r" }
    default abort
  }
  
  puts "Finished OK\n"
')

echo "$VAR"
rm -rf /usr/bin/mim
ln -sf /opt/mim/mim /usr/local/bin/mim