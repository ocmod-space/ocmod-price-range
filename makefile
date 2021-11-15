mod_name=$(shell basename `pwd`)
add_dir=add
arc_dir=arc
src_dir=src
zip_dir=zip
zip_file=$(mod_name).ocmod.zip
pwd_file=hideg.pwd
date=202101010000.00

default: zip

# making zip-file
zip: /usr/local/bin/fcl /usr/local/bin/hideg ver enc
	@if [ -d $(zip_dir) ]; then rm -f "$(zip_dir)/$(zip_file)"; else mkdir -p "$(zip_dir)"; fi

	@echo Set source files time.
	@find "$(src_dir)" -exec touch -a -m -t $(date) {} \;

	@echo Create ZIP.
	@cd "$(src_dir)" && zip -9qrX "../$(zip_dir)/$(zip_file)" *

	@echo
	@echo Module \""$(mod_name)"\" successfully compiled!
	@echo

# set version in install.xml if exist
ver:
	@if [ -f "$(src_dir)"/install.xml ]; then \
		sed -i -e 's/\(<version>\).*\(<\/version>\)/<version>$(shell cat version)<\/version>/g' "$(src_dir)"/install.xml; \
	fi

# packing and encrypting files
enc: pwd
	@echo
	@echo ----------------

	@if [ -f "$(pwd_file)" ]; then \
		mkdir -p "$(arc_dir)"; \
		echo Create FCL.; \
		fcl make -q -f -E".git" -E"$(add_dir)" -E"$(arc_dir)" -E"$(zip_dir)" -E"$(pwd_file)" "$(arc_dir)/$(mod_name)"; \
		echo Encrypt FCL.; \
		hideg "$(arc_dir)/$(mod_name).fcl"; \
		echo Clean.; \
		rm -f "$(arc_dir)/$(mod_name).fcl"; \
	fi

# check pwd-file
pwd:
	@if [ ! -f "$(pwd_file)" ]; then \
		hideg; \
	fi

# extract files from encrypted fcl-file
extr: pwd
	@if [ -f "$(pwd_file)" -a -f "$(arc_dir)/$(mod_name).fcl.g" ]; then \
		hideg "$(arc_dir)/$(mod_name).fcl.g"; \
		fcl extr -f "$(arc_dir)/$(mod_name).fcl"; \
		rm -f "$(arc_dir)/$(mod_name).fcl"; \
	fi

# list files into encrypted fcl-file
list: pwd
	@if [ -f "$(pwd_file)" -a -f "$(arc_dir)/$(mod_name).fcl.g" ]; then \
		hideg "$(arc_dir)/$(mod_name).fcl.g"; \
		fcl list "$(arc_dir)/$(mod_name).fcl"; \
		rm -f "$(arc_dir)/$(mod_name).fcl"; \
	fi
