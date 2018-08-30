<div class="form-group mx-auto col-md-6 ">
    <label id="captcha_label"
           for="captcha">Captcha: {{ config("blogetc.captcha.basic_question", "[error - undefined captcha question]" )}} </label>
    <input type='text' required class="form-control" name='captcha' id="captcha" placeholder=""
           value="{{old("captcha")}}">
</div>