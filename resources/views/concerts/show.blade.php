<h1>{{$concert->title}}</h1>
<p>{{$concert->description}}</p>
{{--<h2>{{$concert->datetime->format('F j, Y')}}</h2>--}}

{{--<h2>{{$concert->formatted_date}}</h2>--}}
<h2>{{$concert->FormattedDate}}</h2>

{{--<h2>{{$concert->datetime->format('g:ia')}}</h2>--}}
<h2>{{$concert->FormattedTime}}</h2>

<h2>{{$concert->DollarsPrice}}</h2>


<h2>{{$concert->venue}}</h2>
<h3>{{$concert->venue_address}}</h3>
<h2>{{$concert->city}}, {{$concert->state}} {{$concert->zip}}</h2>
<p>{{$concert->additional_info}}</p>