
<div id="visitor-tracker__container">
    <div id="visitor-tracker__message">
        <?php if( 1 == $value['count'] ) { ?>
            <p id="visitor-tracker__title"> <?php esc_html_e( 'Hi, You\'re visited our site for the first time.', 'visitor-tracker' ); ?> </p>
        <?php } else { ?>
            <p id="visitor-tracker__title" >Welcome back ! You're visited <span id="visitor-tracker__span"> <?php echo $value['count']; ?> </span> times in our site. You last visit was on <?php echo $value['time']; ?> </p>
        <?php } ?>
    </div>
    <div id="visitor-tracker__notice">
        <p id="visitor-tracker__btn"> <?php esc_html_e( 'Got it', 'visitor-tracker' ); ?> </p>
    </div>
</div>
