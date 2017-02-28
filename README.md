# phpvncinfo
Simple php page to display VNC sessions
A VNC server with xinetd setup is assumed, It is also assumed XDMCP is being handled by lightdm, hence the reference to /var/chache/lightdm
Dependencies:

This script uses snmp monitoring to proble local systems sockets & process so net-snmp package & as well as php-snmp nedds to be installed.
Snmp nedds to be configured to allow read to the following top level OI; .1.3.6.1.2 & .1.3.6.1.4
