import bpy

# Crear cubo

bpy.ops.mesh.primitive_cube_add(size=2, location=(0,0,0))

cube = bpy.context.object

# Rotar el cubo

cube.rotation_euler= (0,0,0)
cube.keyframe_insert(data_path="rotation_euler", frame=1)

cube.rotation_euler = (0,0,3.14159) # 180 grados
cube.keyframe_insert(data_path="rotation_euler", frame=60)

bpy.context.scene.frame_end = 60