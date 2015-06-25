<?php 

	echo "<h2>Contact</h2>\n";
	
	echo "<p>Got a technical or usage question? Check the <a href='/faqs'>FAQs</a> first, and if you can't find the answer you're looking for, use the form here to send a message.</p>\n";

	echo $form->start('contactForm', null, 'post', null, null, array('onSubmit'=>'validateContactForm(); return false;')) . "\n";
	echo $form->input('hidden', 'username', htmlspecialchars($logged_in['username'], ENT_QUOTES)) . "\n";
	/* Name */		echo $form->row('text', 'name', $operators->firstTrue(@$_POST['name'], @$logged_in['full_name']), true, 'Name', 'form-control') . "\n";
	/* Honeypot */	echo $form->input('honeypot', 'company') . "\n";
	/* Timer */		echo $form->input('timer', 'title') . "\n"; // for detecting machine submission
	/* Email */		echo $form->row('email', 'email', $operators->firstTrue(@$_POST['email'], @$logged_in['email']), true, 'Email', 'form-control') . "\n";
	/* Telephone */	echo $form->row('tel', 'telephone', @$_POST['telephone'], false, 'Telephone', 'form-control') . "\n";
	/* Message */	echo $form->row('textarea', 'message', @$_POST['message'], true, 'Message', 'form-control', null, null, array('rows'=>'5')) . "\n";
	/* Actions */	echo $form->row('submit', 'send', 'SEND', null, null, 'btn btn-info') . "\n";	

	echo $form->end();
	
?>