<?php
require_once 'Common/Traits/AttributeTrait.php';
require_once 'Common/Abstracts/FormAbstract.php';
require_once 'Element.php';
require_once 'Form.php';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

<div class="container">
    <h1>Bootstrap demo</h1>

    <?php
    $form = new Form([
        'action' => 'submit.php',
        'method' => 'POST'
    ]);

    echo $form->open();

    echo $form->startElement('div', ['class' => 'mb-3']);
    echo $form->element->label('name', 'Name:', ['class' => 'form-label']);
    echo $form->element->input('text', 'name', ['class' => 'form-control']);
    echo $form->endElement();

    // textarea
    echo $form->startElement('div', ['class' => 'mb-3']);
    echo $form->element->label('message', 'Message:', ['class' => 'form-label']);
    echo $form->element->textarea('message', ['class' => 'form-control']);
    echo $form->endElement();

    // select
    echo $form->startElement('div', ['class' => 'mb-3']);
    $options = [
        '1' => 'Option 1',
        '2' => 'Option 2',
        '3' => 'Option 3'
    ];
    echo $form->element->label('select', 'Select:', ['class' => 'form-label']);
    echo $form->element->select('select', $options, ['class' => 'form-select', 'value' => '2']);
    echo $form->endElement();

    echo $form->startElement('section');
        echo $form->startElement('div', ['class' => 'mb-3 form-check form-switch']);
            // select
            echo $form->startElement('div', ['class' => 'mb-3']);
            $options = [
                '1' => 'Option 1',
                '2' => 'Option 2',
                '3' => 'Option 3'
            ];
            echo $form->element->label('select', 'Select:', ['class' => 'form-label']);
            echo $form->element->select('select', $options, ['class' => 'form-select', 'value' => '2']);
            echo $form->endElement();
        echo $form->endElement();
    echo $form->endElement();



    // gender radio buttons
    $options = [
        'm' => 'Male',
        'f' => 'Female'
    ];

    foreach ($options as $value => $label) {
        echo $form->startElement('div', ['class' => 'mb-3 form-check form-switch']);
        echo $form->element->input('checkbox', 'gender' . $value, [
            'class'   => 'form-check-input',
            'role'    => 'switch',
            'checked' => $value === 'm'
        ]);
        echo $form->element->label('gender' . $value, $label, ['class' => 'form-check-label']);
        echo $form->endElement();
    }

    $form->startElement('section');
        $form->startElement('div', ['class' => 'mb-3 form-check form-switch']);
            // label and other elements
        $form->endElement();
    $form->endElement();


    // agree checkbox
    echo $form->startElement('div', ['class' => 'mb-3 form-check']);
    echo $form->element->input('checkbox', 'agree', ['class' => 'form-check-input']);
    echo $form->element->label('agree', 'I agree to the terms and conditions', ['class' => 'form-check-label']);
    echo $form->endElement();

    // submit button
    echo $form->startElement('name6', ['class' => 'mb-3 text-end']);
    echo $form->element->button('submit', 'submit', 'Submit', ['class' => 'btn btn-primary']);
    echo $form->endElement();
    ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
        crossorigin="anonymous"></script>
</body>
</html>
