#
# Copyright (C) 2014 Nethesis S.r.l.
# http://www.nethesis.it - support@nethesis.it
#
# This script is part of NethServer.
#
# NethServer is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License,
# or any later version.
#
# NethServer is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with NethServer.  If not, see .
#

package NethServer::Samba;

use strict;
use esmith::ConfigDB;

=head1 NethServer::Samba package

Encapsulate the Samba domain logic.

Copyright (C) 2014 Nethesis srl

=head2 new()

Create a new Samba object.

=cut
sub new
{
    my $class = shift;
    my $configDb = esmith::ConfigDB->open_ro();

    my $self = {
	$configDb->get('smb')->props, 
	'ConfigDb' => $configDb
    };

    bless $self, $class;

    return $self;
}

=head2 get_workgroup_name()

Obtain the workgroup name from ConfigDB, applying the default logic.

=cut
sub get_workgroup_name
{
    my $self = shift;
    my $DomainName = $self->{ConfigDb}->get_value('DomainName') || 'localdomain';
    my $defaultWorkgroup = 'WORKGROUP';

    if($self->{ServerRole} =~ /^(PDC|ADS)$/) {
	$defaultWorkgroup = (split('\.', $DomainName))[0];
    }

    return uc($self->{Workgroup} || $defaultWorkgroup);
}

=head2 get_local_sid()

Fetch the local SID from secrets.tdb.

=cut
sub get_local_sid
{
    my $self = shift;   
    if( ! exists $self->{'__localSid'}) {
	$self->_fetch_getlocalsid();
    }
    return $self->{'__localSid'};
}

=head2 get_local_name()

Fetch the local domain/machine name associated to SID

=cut
sub get_local_name
{
    my $self = shift;
    if( ! exists $self->{'__localName'}) {
	$self->_fetch_getlocalsid();
    }   
    return $self->{'__localName'};
}

sub _fetch_getlocalsid
{
    my $self = shift;
    my $out = qx(/usr/bin/net getlocalsid);
    chomp($out);
    $out =~ m/^SID for domain (.*) is: (.*)$/;
    $self->{'__localName'} = $1 || '';      
    $self->{'__localSid'} = $2 || '';
}

=head2 get_sam_prefix()

Retrieve the prefix for `net sam` commands. The prefix depends on the
server role.

=cut
sub get_sam_prefix()
{
    my $self = shift;
    return ($self->{ServerRole} eq 'PDC') ? $self->get_domain_name() : $self->get_local_name();
}

=head2 get_domain_name()

Fetch the domain name from secrets.tdb

=cut
sub get_domain_name()
{
    my $self = shift;
    if( ! exists $self->{'__domainName'}) {
	$self->_fetch_getdomainsid();
    }
    return $self->{'__domainName'};
}

sub _fetch_getdomainsid
{
    my $self = shift;
    my $out = qx(/usr/bin/net getdomainsid);
    chomp($out);
    $out =~ m/^SID for domain (.*) is: (.*)$/m;
    $self->{'__domainName'} = $1 || '';
    $self->{'__domainSid'} = $2 || '';
}


1;
