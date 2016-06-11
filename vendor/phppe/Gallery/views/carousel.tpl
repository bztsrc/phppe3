<!cms imglist(1) carousel>
<div id="carousel" class="carousel slide" data-ride="carousel">
    <ol class="carousel-indicators">
<!foreach carousel>
			<li data-target="#carousel" <!if ordering==0>class="active" <!/if>data-slide-to="<!=ordering>"></li>
<!/foreach>
    </ol>
    <div class="carousel-inner" role="listbox">
<!foreach carousel>
        <div class="item<!if ordering==0> active<!/if>"><img src="gallery/2/<!=id>" alt="" class="img-responsive"></div>
<!/foreach>
    </div>
</div>
