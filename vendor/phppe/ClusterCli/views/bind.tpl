; GENERATED FILE, do not edit!
$TTL	1d ; 1 day ttl
$ORIGIN example.com.
@  1D  IN  SOA ns1.example.com. hostmaster.example.com. (
			      <!=date('YmdH')> ; serial (unique to every hour)
			      5m ; refresh
			      15 ; retry
			      5m ; expire
			      5m ; nxdomain ttl
			     )
       IN  NS     ns1.example.com. ; in the domain
       IN  NS     ns2.example.com. ; external to domain
       IN  MX  10 mail.another.com. ; external mail provider
; server host definitions<br>
<!foreach nodes>
<!if type=='loadbalancer'>
ns1    IN A      <!=id>  ; dns load balancer<br>
<!/if>
<!if type=='master'>
mgmt   IN  CNAME  mgmt<!=IDX><br>
mgmt<!=IDX>  IN  A      <!=id>  ; current master<br>
<!/if>
<!if type=='slave'>
mgmt<!=IDX>   IN A      <!=id>  ; standby slave<br>
<!/if>
<!if type=='worker'>
<!foreach subdomains>
<!=VALUE>    IN A    <!=parent.id><br>
<!/foreach>
<!/if>
<!/foreach>
