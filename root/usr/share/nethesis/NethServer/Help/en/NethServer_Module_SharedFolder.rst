==============
Shared folders
==============

A shared folder is a resource on the system which can be
accessed using SMB/CIFS (Windows File and Printer Sharing) protocol. 

Shared folders use Samba and require an Active Directory account provider, being it **local** (available by installing the ``nethserver-dc`` module from the Software Center page) or **remote** (available by connecting a Microsoft or Samba Active Directory controller). If an LDAP account provider or no account provider is configured at all, only guest mode access is provided.

Create new / edit
-----------------

General
^^^^^^^

Name
    The name of the shared folder. It can only contain lower case letters,
    numbers, dots, dashes and underscores. The maximum length of the name is 12 characters.

Description
    Optional field for a brief description of the shared folder.

Group owner
    The owning group of the shared folder, only members of the
    group can access the folder.

Allow writing to the group owner
    Allow write access to members of the owning group.

Allow read access to all
    Allow read access to anyone who connects to the system, as well as
    public networks.

Guest Access
     A *guest user* is a user whose identification has failed because
     it did not provide credentials or has provided incorrect. For
     users or devices that act in this mode, you can grant the
     following permissions:

     * None
     * Read-only
     * Read and write

Network Recycle Bin
     Collects files deleted from this shared folder, so similar to the
     Windows Recycle Bin.

Keep files of the same name
     If two files have the same name, they remain distinct in trash. By
     disabling this option, the last one overwrites the previous file.


Browseable
     Controls the visibility of the shared folder. When this flag is
     set, the shared folder is listed publicly. This does not affect
     the permission to use this resource.

.. raw:: html

   {{{INCLUDE NethServer_Module_SharedFolder_Plugin_*.html}}}

ACL
^^^

The Access Control List allows specifing access permissions to the
shared folder for each users or groups, in addition to those of the
group owner.

Read
    Allow or deny read access to the user or group selected.

Write 
    Allow or deny write access to the user or group selected.


Delete
------

Removes the folder and all its contents. *The action is not
reversible!* The only way to recover the contents of a shared folder
that has been removed is to restore a backup.

Reset permissions
-----------------

Set the group owner and ACLs configured using this module
on all files in the folder. The operation will be performed recursively on
all files and subfolders in the shared folder.

