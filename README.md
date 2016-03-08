# PROJECT: Mixa #
### Run ###
* *composer install*
* crate database *mixa* with account *root:kingdom*
* set *httpd-vhosts.conf* and */etc/hosts* for domain *mixa.localhost*

### Strucure ###
* **DOCUMENT_ROOT**:*./*
* **RESOURCE_ROOT**:*./www/web* accessible from *http://mixa.localhost/*
* **RESOURCE_SHARED_ROOT**:*./www/shared* accessible from *http://mixa.localhost/shared/*