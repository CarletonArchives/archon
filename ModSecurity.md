# Using ModSecurity with Archon

by Christian von Kleist / LibraryHost LLC / [LibraryHost.com](http://libraryhost.com/)

ModSecurity is an open-source web application firewall that detects and blocks
malicious HTTP activity. In our testing, we discovered ModSecurity to be quite
compatible with Archon.

There was one minor issue. ModSecurity incorrectly flagged some behavior when
editing certain default phrases in Archon's administrative and public user
interfaces (e.g., the "Archon Configuration - Disabled Message" phrase). These
phrases contained one or more Unicode non-breaking space characters (byte
sequence `\xC2\xA0`), which ModSecurity incorrectly thought were suspicious. We
found two separate workarounds for this minor problem:

1. When editing the phrases, simply replace any double space characters between
   sentences with one or more normal space characters.
2. Temporarily disable two ModSecurity rules. See below for more information.

# Installation of ModSecurity with Archon

## Prerequisites

These instructions assume you will be running **Archon under Apache on Ubuntu Linux**.

Before starting setup, update Ubuntu's package index:

```bash
sudo apt-get update
```

## Install packages

```bash
sudo apt-get install libapache2-modsecurity

# Ensure the module is enabled
sudo a2enmod security2

# Restart Apache to load the module
sudo service apache2 restart
```

ModSecurity is now installed and enabled, but it doesn't yet have any
filtering/blocking rules set up.

# Configuring ModSecurity for Archon

ModSecurity configuration happens in three locations:

1. ModSecurity Apache module config file:

    `/etc/apache2/mods-available/security2.conf`

2. ModSecurity system-wide config file:

    `/etc/modsecurity/modsecurity.conf`

3. ModSecurity CRS (core rule set) directory, which we created here:

    `/etc/modsecurity/crs/`

## 1. ModSecurity Apache module config file

This is what our file looked like:

```apache
# /etc/apache2/mods-available/security2.conf
<IfModule security2_module>
        # Default Debian dir for modsecurity's persistent data
        SecDataDir /var/cache/modsecurity

        # Include all the *.conf files in /etc/modsecurity.
        # Keeping your local configuration in that directory
        # will allow for an easy upgrade of THIS file and
        # make your life easier
        IncludeOptional /etc/modsecurity/crs/*.conf
        IncludeOptional /etc/modsecurity/*.conf
</IfModule>
```

## 2. ModSecurity system-wide config file

We started off with the default Ubuntu configuration by copying it into place:

```bash
cd /etc/modsecurity/
sudo cp modsecurity.conf-recommended modsecurity.conf
```

Then we changed these parameters in the file:

```apache
# Block suspicious requests
SecRuleEngine On

# Don't block suspicious responses (because Archon often generates
# legitimate output that looks suspicious)
SecResponseBodyAccess Off

# In our testing the following two rules prevented admins from editing some of
# Archon's  interface messages because the default values include unicode
# non-breaking spaces. We disabled these rules during testing, then re-enabled
# them for production.
SecRuleRemoveById 960024
SecRuleRemoveById 981245
```

## 3. ModSecurity CRS (core rule set) directory

We created the directory `/etc/modsecurity/crs` to contain the filter rule sets
we wanted to use.

```bash
sudo mkdir /etc/modsecurity/crs
```

We copied all of ModSecurity's base rule sets into
`/usr/share/modsecurity-crs`.

```bash
cd /usr/share/modsecurity-crs/
sudo cp base_rules/* /etc/modsecurity/crs/
```

# Finalizing installation

After configuring ModSecurity for Archon, restart Apache again.

```bash
sudo service apache2 restart
```

## Testing your setup

To verify that ModSecurity is working with Archon, try using Archon's search
box in the public interface to search for this text, which ModSecurity will
definitely see as an attempted cross-site scripting attack:

```javascript
<script>alert(444)</script>
```

... or this text, which ModSecurity will detect as an SQL injection attack:

```
" OR 1=1; --
```

In each case, ModSecurity should block the request and return a "403 Forbidden"
error message. You can also look in the following log file to see information
about the blocked request:

```
/var/log/apache2/modsec_audit.log
```

Here is an example log message:

```
--6801bd22-H--
Message: Access denied with code 403 (phase 2). Pattern match "(^[\"'`\xc2\xb4\xe2\x80\x99\xe2\x80\x98;]+|[\"'`\xc2\xb4\xe2\x80\x99\xe2\x80\x98;]+$)" at ARGS:q. [file "/etc/modsecurity/crs/modsecurity_crs_41_sql_injection_attacks.conf"] [line "64"] [id "981318"] [rev "2"] [msg "SQL Injection Attack: Common Injection Testing Detected"] [data "Matched Data: \x22 found within ARGS:q: \x22 OR 1=1; --"] [severity "CRITICAL"] [ver "OWASP_CRS/2.2.8"] [maturity "9"] [accuracy "8"] [tag "OWASP_CRS/WEB_ATTACK/SQL_INJECTION"] [tag "WASCTC/WASC-19"] [tag "OWASP_TOP_10/A1"] [tag "OWASP_AppSensor/CIE1"] [tag "PCI/6.5.2"]
Action: Intercepted (phase 2)
Apache-Handler: application/x-httpd-php
Stopwatch: 1463393312189867 1886 (- - -)
Stopwatch2: 1463393312189867 1886; combined=983, p1=302, p2=600, p3=0, p4=0, p5=81, sr=48, sw=0, l=0, gc=0
Response-Body-Transformed: Dechunked
Producer: ModSecurity for Apache/2.7.7 (http://www.modsecurity.org/); OWASP_CRS/2.2.8.
Server: Apache/2.4.7 (Ubuntu)
Engine-Mode: "ENABLED"

--6801bd22-Z--
```
