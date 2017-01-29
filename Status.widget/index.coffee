# Code originally created by the awesome members of Ubersicht community.
# I stole from so many I can't remember who you are, thank you so much everyone!
# Haphazardly adjusted and mangled by Pe8er (https://github.com/Pe8er)

options =
  # Easily enable or disable the widget.
  widgetEnable: true

  # Set the start date to count from.
  theDate     : "03/30/2017"

  # The name of the event
  name        : "Project Status"

command: "sh 'AnotherSidebar.widget/Status.widget/status.sh'"

refreshFrequency: '1h'

style: """
  white1 = rgba(white,1)
  white05 = rgba(white,0.5)
  white02 = rgba(white,0.2)
  black02 = rgba(black,0.2)

  position relative
  background-color rgba(0,0,0,.2)
  -webkit-backdrop-filter blur(30px) brightness(80%) contrast(100%) saturate(140%)
  margin-top 1px


  width 176px
  overflow hidden
  white-space nowrap

  *, *:before, *:after
    box-sizing border-box

  .wrapper
    position: relative
    font-size 8pt
    line-height 11pt
    color white
    padding 8px
    align-items center
    display flex

  .box_title
    width 100%
    float left
    text-align center
    color white00

  .box
    width 33%
    float left
    text-align center
    color white05

  .box span
    display block

  .box span:first-of-type
    font-weight 700
    color white

  .green
    color #050
  .yellow
    background-color rgba(255,255,0,.2)
    color #FFF
  .red
    background-color rgba(255,0,0,.2)
    color #FFF
"""

options : options

render: (output) ->

  # Initialize our HTML.
  elapsedHTML = ''

  # Get our pieces.
  values = output.slice(0,-1).split(":")

  # Create the DIVs for each piece of data.
  elapsedHTML = "
  	<div class='wrapper sidebar'>
    	<div class='box_title'>
			#{options.name}
		</div>
	</div>
    <div class='wrapper sidebar'>
      <div class='box " + values[1] + "'>
        <span>" + values[0] + "</span>
        <span>" + values[2] + "</span>
      </div>
      <div class='box " + values[4] + "'>
        <span>" + values[3] + "</span>
        <span>" + values[5] + "</span>
      </div>
      <div class='box " + values[7] + "'>
        <span>" + values[6] + "</span>
        <span>" + values[8] + "</span>
      </div>
    </div>
    <div class='wrapper sidebar'>
      <div class='box " + values[10] + "'>
        <span>" + values[9] + "</span>
        <span>" + values[11] + "</span>
      </div>
      <div class='box " + values[13] + "'>
        <span>" + values[12] + "</span>
        <span>" + values[14] + "</span>
      </div>
      <div class='box " + values[16] + "'>
        <span>" + values[15] + "</span>
        <span>" + values[17] + "</span>
      </div>
    </div>"
  return elapsedHTML

# Update the rendered output.
update: (output, domEl) ->

  # Get our main DIV.
  div = $(domEl)

  if @options.widgetEnable
    # Get our pieces.
    values = output.slice(0,-1).split(" ")

    # Initialize our HTML.
    elapsedHTML = ''

    # Sort out flex-box positioning.
    div.parent('div').css('order', '1')
    div.parent('div').css('flex', '0 1 auto')
  else
    div.remove()
