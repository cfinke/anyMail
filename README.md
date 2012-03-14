anyMail was an IMAP Web e-mail client I wrote during an independent study course in college. I'm publishing the code for posterity; you really shouldn't use it.  Bits and pieces of it might prove useful as general examples, but I haven't looked at this code in almost a decade, and it is most certainly inefficient and insecure.

Logging In
==========

To login, simply fill in your e-mail address and mail account password on the main anyMail screen (index.php).  anyMail will attempt to determine your mail host, mail protocol, and username.  If it is unable to do so, you will be asked to specify these three values.  Your ISP or email provider should have given you these values.

Reading E-mail
==============

The main screen of anyMail is split up into three sections: the message listing pane (1), the message viewing pane (2), and the supplementary information column (3).  When you login, anyMail will download any new messages that you have and will show them in the message listing pane.  From here, if you click on the subject of the message, it will appear in the message viewing pane.

In the message listing pane, you can sort by either Date, Sender, or Subject by clicking on the appropriate column header.  If the messages are already sorted by that column, anyMail will reverse the sort order.

The message viewing pane is split up into three main parts: the action bar (1), the information and attachment pane (2), and the message body (3).

The action bar allows you to perform five main actions on the message you are currently viewing: Reply (1), Reply to All (2), Forward (3), Delete (4), and Label (5).  (Labels will be discussed later on.) The action bar also shows the date that the message was received.

The information and attachment pane shows the subject of the message, the sender, and the receiver.  It also provides a drop-down menu of all of the attachments contained in the message for easy downloading: just click on the menu, and select the file you wish to download.

The message body shows the actual text of the e-mail.  If the e-mail is part of a conversation, a conversation overview will be shown at the end of the e-mail.

Thread Arcs
===========

A thread arc is a visual representation of an e-mail conversation.  anyMail creates a thread arc for each conversation consisting of two or more messages and displays it in the Thread Arc information pane.  For more information on thread arcs, see [IBM's ReMail research project](http://www.research.ibm.com/remail/threadarcs.html)

Sending E-mail
==============

There are several ways that you can begin composing an e-mail to send.  You can choose Compose from the top menu, you can choose Reply, Reply to All, or Forward, from the action bar of the message you are viewing, or you can use the Contacts information pane to initialize a message.  (The Contacts pane will be discussed later.)

You can specify recipients in the To, Cc (carbon copy), and Bcc (blind carbon copy) fields, as well as a subject in the Subject field.  You can also add recipients  by clicking on the To, Cc, and Bcc links next to the contacts and contact groups in the Contacts pane.  

Add an unlimited number of attachments to the message by uploading the file in the Attachments field, and then type your message below.  Send it off by clicking the "Send Message" button, or if you decide to stop writing, click the "Discard" button to return to the main page.
Address Book

The anyMail address book allows you to create contacts and contact groups.  To access the address book, simply click on the Contacts tab in the top menu.

To create a contact, just click on the "New Contact" link.  You will be prompted for a name and an e-mail address.  Once you have added a contact, it will show up in both the Contacts page and the Contacts information pane, in the right-hand column.  You can create an e-mail to a contact by clicking on the To, Cc, of Bcc link next to the contact's name.  (You can also just click on the contact's name in the information pane.)

A contact group is just a collection of contacts that you frequently need to e-mail together.  For example, if you were working on a project with five people and needed to e-mail a status report each day, you might create a contact group containing your group members' addresses.  Then, you could create an e-mail message to all five of your group members by just clicking on the contact group name in the information pane.

To create a contact group, check the boxes next to the contacts that you want to include.  Then, click on the "New Group From Checked" link.  Specify a name for the group, and you're done.  The contact group will then be shown above the individual contacts in the information pane.

Managing Your Mail
==================

anyMail provides two main functions for e-mail management: labels and message archiving.

Labels
======

Labels provide a method for classifying your e-mail.  When you label a message, you are applying a virtual label to the message, just as if you were sticking a label on a file folder in real life.  You can apply zero or more labels to each message.

anyMail includes three types of labels: user-defined, message-state labels, and automatic labels.  These three types are shown in the three label sections in the right-hand column of anyMail

User-defined labels are labels that you create.  You can create labels by either going to the label page (by clicking on the "Labels" link in the top menu) or by selecting "New Label" when you are labeling an individual message.  A message can be labeled with zero or more user-defined labels.

There are three message-state labels: Received, Sent, and Trash.  A message can be labeled with only one of these at a time.

The set of automatic labels includes the following: 

* Unread Messages
* Last 7 Days: This label is applied to all messages received in the last seven days.
* Last 7 Days Unreplied: This label is applied to all messages received in the last seven days that you have not replied to.
* From Address Book Contacts: This is applied to all messages from contacts in your address book.
* From Previous Contacts: This is applied to all messages written by people from whom you have previously received a message.
* From First-time Contacts: This is applied to all messages written by people from whom you have never received a messages.

The rules for viewing messages by their labels are as follows:

* You must have one of the message-state labels selected at all times.
* You can have zero or one of the user-defined labels selected at any given point.
* You can have zero or one of the automatic labels selected at any given point.

You can label a message in one of two ways:

* While viewing the message, you can select the label you wish to apply to it from the "Label this Message" dropdown in the action bar.
* After checking the box next to the message in the listing pane, you can select the label you wish to apply to it from the "Perform Action" dropdown at the top of the page.

If a message is already labeled with the label you select, performing either of these two actions will remove the label.

Message Archiving 
=================

When you log into anyMail, the messages shown in the message listing pane are your unarchived messages.  Archiving a message simply removes it from this default view.  To archive a message, check the box next to it in the listing pane and select "Archive Checked Messages" from the dropdown at the top of the page.

Deleting Messages
=================

Deleting a message in anyMail applies to "Trash" message-state label to it.  In order to completely delete the message from you account, you must first move it to the Trash and then select "Empty Trash" from the dropdown at the top of the screen.

Uploading Messages
==================

anyMail allows you to upload e-mail messages that you might have saved on your computer. To access the upload page, click on the "Upload" link in the top menu.  Here you can specify the file you wish to upload and whether it was sent by you. After the message is uploaded, it will appear in the message listing pane.